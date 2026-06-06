/**
 * @format
 */

import 'react-native-gesture-handler';
import { AppRegistry } from 'react-native';
import App from './App';
import { name as appName } from './app.json';

// Register background fetch headless task
import './backgroundFetch';

// Register call log sync headless task (triggered by native broadcast receiver)
import CallLogSyncTask from './CallLogSyncTask';
AppRegistry.registerHeadlessTask('CallLogSync', () => CallLogSyncTask);

// Register periodic sync headless task (triggered by AlarmManager)
import PeriodicSyncTask from './PeriodicSyncTask';
AppRegistry.registerHeadlessTask('PeriodicSync', () => PeriodicSyncTask);

AppRegistry.registerComponent(appName, () => App);
