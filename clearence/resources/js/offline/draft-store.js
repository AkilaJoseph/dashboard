/**
 * draft-store.js
 *
 * IndexedDB CRUD for offline clearance drafts and officer approval actions.
 * DB: "acims-offline"  version: 2
 * Object stores:
 *   "drafts"          — student clearance request drafts
 *   "officer_actions" — officer approve/reject actions queued while offline
 *
 * Draft shape:
 * {
 *   id:               string  (UUID v4, local primary key)
 *   academic_year:    string
 *   semester:         string
 *   clearance_type:   string  (graduation|semester|withdrawal|transfer)
 *   reason:           string|null
 *   attachments:      Array<{name, blob, mime}>  (always [] until file upload is added)
 *   csrf_token:       string  (captured at save time; used by SW during BG sync)
 *   created_at:       string  (ISO-8601)
 *   status:           'pending' | 'pending_sync' | 'syncing' | 'synced' | 'failed'
 *   server_id:        number|null  (clearance.id after successful sync)
 *   idempotency_key:  string  (UUID v4, sent as X-Idempotency-Key header)
 *   error:            string|null  (last sync error message)
 * }
 *
 * Officer action shape:
 * {
 *   id:               string  (UUID v4, local primary key)
 *   approval_id:      number  (server-side approval record ID)
 *   action:           'approve' | 'reject'
 *   comments:         string|null
 *   csrf_token:       string
 *   created_at:       string  (ISO-8601)
 *   status:           'pending' | 'pending_sync' | 'syncing' | 'synced' | 'failed'
 *   idempotency_key:  string
 *   error:            string|null
 * }
 */

import { openDB } from 'idb';

const DB_NAME    = 'acims-offline';
const DB_VERSION = 2;
const STORE      = 'drafts';
const STORE_OA   = 'officer_actions';

function getDB() {
    return openDB(DB_NAME, DB_VERSION, {
        upgrade(db, oldVersion) {
            if (oldVersion < 1) {
                const store = db.createObjectStore(STORE, { keyPath: 'id' });
                store.createIndex('status',    'status',           { unique: false });
                store.createIndex('created_at','created_at',       { unique: false });
                store.createIndex('idem_key',  'idempotency_key',  { unique: true  });
            }
            if (oldVersion < 2) {
                if (!db.objectStoreNames.contains(STORE_OA)) {
                    const oa = db.createObjectStore(STORE_OA, { keyPath: 'id' });
                    oa.createIndex('status',    'status',           { unique: false });
                    oa.createIndex('created_at','created_at',       { unique: false });
                    oa.createIndex('idem_key',  'idempotency_key',  { unique: true  });
                }
            }
        },
    });
}

/** Generate a RFC-4122 v4 UUID using the Web Crypto API. */
function uuid() {
    return crypto.randomUUID
        ? crypto.randomUUID()
        : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
              (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16)
          );
}

/**
 * Save a new draft.
 * @param {object} fields  { academic_year, semester, clearance_type, reason, attachments?, csrf_token? }
 * @returns {Promise<string>}  the draft's local UUID
 */
export async function saveDraft(fields) {
    const db    = await getDB();
    const draft = {
        id:               uuid(),
        academic_year:    fields.academic_year   ?? '',
        semester:         fields.semester         ?? '',
        clearance_type:   fields.clearance_type   ?? '',
        reason:           fields.reason           ?? null,
        attachments:      fields.attachments      ?? [],
        csrf_token:       fields.csrf_token        ?? '',
        created_at:       new Date().toISOString(),
        status:           'pending',
        server_id:        null,
        idempotency_key:  uuid(),
        error:            null,
    };
    await db.put(STORE, draft);
    return draft.id;
}

/**
 * Retrieve a single draft by its local UUID.
 * @param {string} id
 * @returns {Promise<object|undefined>}
 */
export async function getDraft(id) {
    const db = await getDB();
    return db.get(STORE, id);
}

/**
 * Return all drafts, newest first.
 * @param {'pending'|'syncing'|'synced'|'failed'|null} statusFilter  null = all
 * @returns {Promise<object[]>}
 */
export async function listDrafts(statusFilter = null) {
    const db  = await getDB();
    const all = await db.getAll(STORE);
    const sorted = all.sort((a, b) => (a.created_at < b.created_at ? 1 : -1));
    return statusFilter ? sorted.filter(d => d.status === statusFilter) : sorted;
}

/**
 * Hard-delete a draft.
 * @param {string} id
 */
export async function deleteDraft(id) {
    const db = await getDB();
    await db.delete(STORE, id);
}

/**
 * Mark a draft as successfully synced.
 * @param {string} id        local UUID
 * @param {number} serverId  the clearance.id returned by the server
 */
export async function markSynced(id, serverId) {
    const db    = await getDB();
    const draft = await db.get(STORE, id);
    if (!draft) return;
    await db.put(STORE, { ...draft, status: 'synced', server_id: serverId, error: null });
}

/**
 * Update the status of a draft (e.g. 'syncing', 'failed').
 * @param {string} id
 * @param {'pending'|'syncing'|'synced'|'failed'} status
 * @param {string|null} error
 */
export async function updateDraftStatus(id, status, error = null) {
    const db    = await getDB();
    const draft = await db.get(STORE, id);
    if (!draft) return;
    await db.put(STORE, { ...draft, status, error });
}

/**
 * Return the count of drafts with status 'pending' or 'failed'.
 * @returns {Promise<number>}
 */
export async function pendingCount() {
    const drafts = await listDrafts();
    return drafts.filter(d => d.status === 'pending' || d.status === 'failed').length;
}

// ── Officer action CRUD ───────────────────────────────────────────────────────

/**
 * Save an officer approval/rejection action for later sync.
 * @param {object} fields  { approval_id, action, comments?, csrf_token? }
 * @returns {Promise<string>}  local UUID
 */
export async function saveOfficerAction(fields) {
    const db     = await getDB();
    const record = {
        id:               uuid(),
        approval_id:      fields.approval_id,
        action:           fields.action,
        comments:         fields.comments    ?? null,
        csrf_token:       fields.csrf_token   ?? '',
        created_at:       new Date().toISOString(),
        status:           'pending',
        idempotency_key:  uuid(),
        error:            null,
    };
    await db.put(STORE_OA, record);
    return record.id;
}

/**
 * Return all officer actions, newest first.
 * @returns {Promise<object[]>}
 */
export async function listOfficerActions() {
    const db  = await getDB();
    const all = await db.getAll(STORE_OA);
    return all.sort((a, b) => (a.created_at < b.created_at ? 1 : -1));
}

/**
 * Update the status of an officer action.
 * @param {string} id
 * @param {string} status
 * @param {string|null} error
 */
export async function updateOfficerActionStatus(id, status, error = null) {
    const db     = await getDB();
    const record = await db.get(STORE_OA, id);
    if (!record) return;
    await db.put(STORE_OA, { ...record, status, error });
}

/**
 * Mark an officer action as successfully synced.
 * @param {string} id  local UUID
 */
export async function markOfficerActionSynced(id) {
    const db     = await getDB();
    const record = await db.get(STORE_OA, id);
    if (!record) return;
    await db.put(STORE_OA, { ...record, status: 'synced', error: null });
}
