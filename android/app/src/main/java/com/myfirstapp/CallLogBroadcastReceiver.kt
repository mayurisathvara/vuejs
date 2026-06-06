package com.myfirstapp

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.telephony.TelephonyManager
import android.util.Log
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import java.util.concurrent.TimeUnit

class CallLogBroadcastReceiver : BroadcastReceiver() {
    
    companion object {
        private const val TAG = "CallLogReceiver"
        private var lastState = TelephonyManager.CALL_STATE_IDLE
    }

    override fun onReceive(context: Context, intent: Intent) {
        try {
            val state = intent.getStringExtra(TelephonyManager.EXTRA_STATE)
            Log.d(TAG, "Call state changed: $state")

            when (state) {
                TelephonyManager.EXTRA_STATE_RINGING -> {
                    Log.d(TAG, "Phone is ringing")
                    lastState = TelephonyManager.CALL_STATE_RINGING
                }
                TelephonyManager.EXTRA_STATE_OFFHOOK -> {
                    Log.d(TAG, "Call is active")
                    lastState = TelephonyManager.CALL_STATE_OFFHOOK
                }
                TelephonyManager.EXTRA_STATE_IDLE -> {
                    Log.d(TAG, "Call ended")
                    if (lastState != TelephonyManager.CALL_STATE_IDLE) {
                        // Call just ended, trigger sync via WorkManager (Android 14+ compatible)
                        Log.d(TAG, "Triggering background sync via WorkManager")
                        triggerBackgroundSyncViaWorker(context)
                    }
                    lastState = TelephonyManager.CALL_STATE_IDLE
                }
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error in CallLogBroadcastReceiver", e)
        }
    }

    private fun triggerBackgroundSyncViaWorker(context: Context) {
        try {
            Log.d(TAG, "Scheduling WorkManager task for call log sync")
            
            // Use WorkManager to schedule immediate one-time work
            // WorkManager is exempt from background service restrictions
            val syncWorkRequest = OneTimeWorkRequestBuilder<CallLogSyncWorker>()
                .setInitialDelay(3, TimeUnit.SECONDS) // Wait 3 seconds for call log to be written
                .addTag("call_log_sync")
                .build()
            
            WorkManager.getInstance(context).enqueue(syncWorkRequest)
            
            Log.d(TAG, "WorkManager task scheduled successfully")
        } catch (e: Exception) {
            Log.e(TAG, "Error scheduling WorkManager task", e)
        }
    }
}
