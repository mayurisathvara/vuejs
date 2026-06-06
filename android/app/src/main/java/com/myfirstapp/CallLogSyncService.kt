package com.myfirstapp

import android.app.*
import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.IBinder
import android.util.Log
import androidx.core.app.NotificationCompat
import com.facebook.react.HeadlessJsTaskService
import com.facebook.react.bridge.Arguments
import com.facebook.react.jstasks.HeadlessJsTaskConfig

class CallLogSyncService : HeadlessJsTaskService() {

    companion object {
        private const val TAG = "CallLogSyncService"
        private const val NOTIFICATION_ID = 9999
        private const val CHANNEL_ID = "call_log_sync_service"
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        Log.d(TAG, "Service started")
        
        try {
            // Create notification channel for Android O+
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                createNotificationChannel()
            }
            
            // Start as foreground service with notification
            val notification = createNotification()
            startForeground(NOTIFICATION_ID, notification)
            
            Log.d(TAG, "Foreground service started")
        } catch (e: Exception) {
            Log.e(TAG, "Error starting foreground service", e)
        }
        
        return super.onStartCommand(intent, flags, startId)
    }

    override fun getTaskConfig(intent: Intent?): HeadlessJsTaskConfig? {
        return intent?.let {
            HeadlessJsTaskConfig(
                "CallLogSync",
                Arguments.createMap(),
                60000, // 60 second timeout
                true // allow in foreground
            )
        }
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Call Log Sync Service",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Syncing call logs in background"
                setShowBadge(false)
            }
            
            val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }

    private fun createNotification(): Notification {
        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("Syncing Call Logs")
            .setContentText("Uploading call logs...")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .setOngoing(true)
            .build()
    }

    override fun onHeadlessJsTaskFinish(taskId: Int) {
        super.onHeadlessJsTaskFinish(taskId)
        Log.d(TAG, "Headless task finished, stopping service")
        stopForeground(true)
        stopSelf()
    }
}
