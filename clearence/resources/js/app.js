import './bootstrap';
import './pwa/register-sw';
import './offline/offline-form';
import './offline/officer-sync';
import { initSyncManager } from './offline/sync-manager';

initSyncManager();
