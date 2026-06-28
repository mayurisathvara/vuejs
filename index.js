/**
 * @format
 */

import 'react-native-gesture-handler';
import { AppRegistry } from 'react-native';
import App from './App';
import { name as appName } from './app.json';

// Register background fetch headless task
import './src/workers/backgroundFetch';

// Register call log sync headless task (triggered by native CallLogSyncService)
import CallLogSyncTask from './src/workers/callLogSync';
AppRegistry.registerHeadlessTask('CallLogSync', () => CallLogSyncTask);

AppRegistry.registerComponent(appName, () => App);
