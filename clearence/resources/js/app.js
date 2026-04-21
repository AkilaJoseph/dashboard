import './bootstrap';
import './pwa/register-sw';
import './offline/offline-form';
import { initSyncManager } from './offline/sync-manager';

initSyncManager();
