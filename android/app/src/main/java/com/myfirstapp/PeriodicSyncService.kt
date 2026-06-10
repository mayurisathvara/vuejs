package com.myfirstapp

import android.content.Intent
import android.util.Log
import com.facebook.react.HeadlessJsTaskService
import com.facebook.react.bridge.Arguments
import com.facebook.react.jstasks.HeadlessJsTaskConfig

class PeriodicSyncService : HeadlessJsTaskService() {
    companion object {
        private const val TAG = "PeriodicSyncService"
    }

    override fun getTaskConfig(intent: Intent?): HeadlessJsTaskConfig {
        return HeadlessJsTaskConfig(
            "PeriodicSync", // Task name registered in JS
            Arguments.createMap(),
            30000, // 30 second timeout
            true // Allow task in foreground
        )
    }

    override fun onHeadlessJsTaskStart(taskId: Int) {
        super.onHeadlessJsTaskStart(taskId)
        Log.d(TAG, "Headless task started: $taskId")
    }

    override fun onHeadlessJsTaskFinish(taskId: Int) {
        super.onHeadlessJsTaskFinish(taskId)
        Log.d(TAG, "Headless task finished: $taskId")
    }
}
