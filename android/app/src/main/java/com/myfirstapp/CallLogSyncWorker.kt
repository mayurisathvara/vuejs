package com.myfirstapp

import android.content.Context
import android.util.Log
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import androidx.work.ForegroundInfo
import androidx.core.app.NotificationCompat
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.facebook.react.ReactApplication
import com.facebook.react.ReactInstanceManager
import com.facebook.react.bridge.ReactContext
import com.facebook.react.modules.core.DeviceEventManagerModule
import kotlinx.coroutines.delay

class CallLogSyncWorker(
    private val context: Context,
    params: WorkerParameters
) : CoroutineWorker(context, params) {

    companion object {
        private const val TAG = "CallLogSyncWorker"
        private const val CHANNEL_ID = "call_log_sync_channel"
        private const val NOTIFICATION_ID = 1001
    }

    override suspend fun doWork(): Result {
        return try {
            Log.d(TAG, "Starting call log sync worker")
            
            // Create notification channel for Android O+
            createNotificationChannel()
            
            // Set as foreground to avoid being killed
            setForeground(createForegroundInfo())
            
            // Trigger React Native sync via event
            triggerReactNativeSync()
            
            // Wait a bit for sync to complete (max 60 seconds)
            delay(60000)
            
            Log.d(TAG, "Worker completed successfully")
            Result.success()
        } catch (e: Exception) {
            Log.e(TAG, "Error in sync worker", e)
            Result.retry()
        }
    }

    private fun triggerReactNativeSync() {
        try {
            val reactApplication = context.applicationContext as? ReactApplication
            val reactInstanceManager = reactApplication?.reactNativeHost?.reactInstanceManager
            val reactContext = reactInstanceManager?.currentReactContext
            
            if (reactContext != null) {
                Log.d(TAG, "Triggering React Native sync via event")
                reactContext
                    .getJSModule(DeviceEventManagerModule.RCTDeviceEventEmitter::class.java)
                    ?.emit("CallLogSyncTrigger", null)
            } else {
                Log.w(TAG, "React context not available, sync will happen on next app open")
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error triggering React Native sync", e)
        }
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Call Log Sync",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Syncing call logs in background"
            }
            
            val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }

    private fun createForegroundInfo(): ForegroundInfo {
        val notification = NotificationCompat.Builder(context, CHANNEL_ID)
            .setContentTitle("Syncing Call Logs")
            .setContentText("Processing call logs...")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setOngoing(true)
            .build()

        return ForegroundInfo(NOTIFICATION_ID, notification)
    }
}
