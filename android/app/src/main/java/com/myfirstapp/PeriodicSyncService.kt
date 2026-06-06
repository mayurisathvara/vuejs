package com.myfirstapp

import android.app.AlarmManager
import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log
import com.facebook.react.HeadlessJsTaskService
import com.facebook.react.bridge.Arguments
import com.facebook.react.jstasks.HeadlessJsTaskConfig

/**
 * Periodic Sync using AlarmManager
 * This can wake up the app even when completely killed
 */
class PeriodicSyncReceiver : BroadcastReceiver() {
    companion object {
        private const val TAG = "PeriodicSync"
        private const val SYNC_INTERVAL = 15 * 60 * 1000L // 15 minutes

        fun schedule(context: Context, alignToClock: Boolean = false) {
            val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
            val intent = Intent(context, PeriodicSyncReceiver::class.java)
            val pendingIntent = PendingIntent.getBroadcast(
                context,
                1001,
                intent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
            )

            // Cancel existing alarm
            alarmManager.cancel(pendingIntent)

            // Calculate next trigger time
            val triggerTime = if (alignToClock) {
                // Align to clock intervals: :00, :15, :30, :45
                val now = System.currentTimeMillis()
                val calendar = java.util.Calendar.getInstance()
                calendar.timeInMillis = now
                
                // Get current minute
                val currentMinute = calendar.get(java.util.Calendar.MINUTE)
                
                // Find next 15-minute mark (0, 15, 30, 45)
                val nextMark = ((currentMinute / 15) + 1) * 15
                
                if (nextMark >= 60) {
                    // Roll over to next hour
                    calendar.add(java.util.Calendar.HOUR_OF_DAY, 1)
                    calendar.set(java.util.Calendar.MINUTE, 0)
                } else {
                    calendar.set(java.util.Calendar.MINUTE, nextMark)
                }
                calendar.set(java.util.Calendar.SECOND, 0)
                calendar.set(java.util.Calendar.MILLISECOND, 0)
                
                val alignedTime = calendar.timeInMillis
                Log.d(TAG, "Clock-aligned next sync at: ${java.text.SimpleDateFormat("HH:mm:ss", java.util.Locale.getDefault()).format(alignedTime)}")
                alignedTime
            } else {
                // Simple: 15 minutes from now
                val simpleTime = System.currentTimeMillis() + SYNC_INTERVAL
                Log.d(TAG, "Next sync in 15 minutes from now: ${java.text.SimpleDateFormat("HH:mm:ss", java.util.Locale.getDefault()).format(simpleTime)}")
                simpleTime
            }

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                // For Android 6.0+, use setExactAndAllowWhileIdle to work even in Doze mode
                alarmManager.setExactAndAllowWhileIdle(
                    AlarmManager.RTC_WAKEUP,
                    triggerTime,
                    pendingIntent
                )
            } else {
                alarmManager.setExact(
                    AlarmManager.RTC_WAKEUP,
                    triggerTime,
                    pendingIntent
                )
            }

            val minutesUntil = (triggerTime - System.currentTimeMillis()) / 60000
            Log.d(TAG, "Scheduled next sync in $minutesUntil minutes")
        }

        fun cancel(context: Context) {
            val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
            val intent = Intent(context, PeriodicSyncReceiver::class.java)
            val pendingIntent = PendingIntent.getBroadcast(
                context,
                1001,
                intent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
            )
            alarmManager.cancel(pendingIntent)
            Log.d(TAG, "Cancelled periodic sync")
        }
    }

    override fun onReceive(context: Context, intent: Intent) {
        Log.d(TAG, "Periodic sync triggered")

        // Start the headless task service
        val serviceIntent = Intent(context, PeriodicSyncService::class.java)
        context.startService(serviceIntent)

        // Schedule next alarm
        schedule(context)
    }
}

/**
 * Headless JS Task Service for periodic sync
 */
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
