package com.myfirstapp

import com.facebook.react.bridge.ReactApplicationContext
import com.facebook.react.bridge.ReactContextBaseJavaModule
import com.facebook.react.bridge.ReactMethod
import android.content.Intent
import android.os.Build
import android.app.Activity
import android.app.Application
import android.content.Context

class CallModule(reactContext: ReactApplicationContext) : ReactContextBaseJavaModule(reactContext) {
    override fun getName() = "CallModule"

    @ReactMethod
    fun startCallMonitoring() {
        val context = reactApplicationContext
        val serviceIntent = Intent(context, CallService::class.java)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.startForegroundService(serviceIntent)
        } else {
            context.startService(serviceIntent)
        }
    }
} 