package com.myfirstapp

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.telephony.TelephonyManager
import android.util.Log
import android.content.SharedPreferences

class CallReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        val state = intent.getStringExtra(TelephonyManager.EXTRA_STATE)
        val number = intent.getStringExtra(TelephonyManager.EXTRA_INCOMING_NUMBER)
        Log.d("CallReceiver", "State: $state, Number: $number")

        val prefs: SharedPreferences = context.getSharedPreferences("call_prefs", Context.MODE_PRIVATE)
        val editor = prefs.edit()
        val now = System.currentTimeMillis()

        when (state) {
            TelephonyManager.EXTRA_STATE_RINGING -> {
                editor.putLong("ringing_time", now)
                editor.putString("ringing_number", number)
                editor.apply()
            }
            TelephonyManager.EXTRA_STATE_OFFHOOK -> {
                editor.putLong("offhook_time", now)
                editor.apply()
            }
            TelephonyManager.EXTRA_STATE_IDLE -> {
                editor.putLong("idle_time", now)
                editor.apply()
            }
        }

        val serviceIntent = Intent(context, CallService::class.java)
        serviceIntent.putExtra("state", state)
        serviceIntent.putExtra("number", number)
        context.startService(serviceIntent)
    }
} 