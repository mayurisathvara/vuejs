package com.myfirstapp

import android.app.AlarmManager
import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log

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

            alarmManager.cancel(pendingIntent)

            val triggerTime = if (alignToClock) {
                val now = System.currentTimeMillis()
                val calendar = java.util.Calendar.getInstance()
                calendar.timeInMillis = now
                val currentMinute = calendar.get(java.util.Calendar.MINUTE)
                val nextMark = ((currentMinute / 15) + 1) * 15
                if (nextMark >= 60) {
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
                val simpleTime = System.currentTimeMillis() + SYNC_INTERVAL
                Log.d(TAG, "Next sync in 15 minutes: ${java.text.SimpleDateFormat("HH:mm:ss", java.util.Locale.getDefault()).format(simpleTime)}")
                simpleTime
            }

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                alarmManager.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerTime, pendingIntent)
            } else {
                alarmManager.setExact(AlarmManager.RTC_WAKEUP, triggerTime, pendingIntent)
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
        val serviceIntent = Intent(context, PeriodicSyncService::class.java)
        context.startService(serviceIntent)
        schedule(context)
    }
}
