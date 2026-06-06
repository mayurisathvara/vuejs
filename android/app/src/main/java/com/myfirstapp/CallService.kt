package com.myfirstapp

import android.app.Service
import android.content.Context
import android.content.Intent
import android.os.IBinder
import android.util.Log
import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import androidx.core.app.NotificationCompat
import android.widget.Toast
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import android.os.Handler
import android.os.Looper
import android.telephony.TelephonyManager
import android.provider.CallLog
import android.database.Cursor
import android.content.ContentResolver
import android.provider.ContactsContract
import org.json.JSONObject
import java.io.OutputStream
import java.net.HttpURLConnection
import java.net.URL
import kotlin.concurrent.thread
import androidx.work.*
import android.provider.Settings
import android.view.WindowManager
import android.view.LayoutInflater
import android.view.View
import android.widget.TextView
import android.graphics.PixelFormat
import android.provider.Settings.Secure
import android.widget.ImageView
import com.myfirstapp.R
import android.widget.Button

class CallService : Service() {
    override fun onBind(intent: Intent?): IBinder? = null

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        Log.d("CallService", "Service started")
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channelId = "call_monitor_channel"
            val channelName = "Call Monitor"
            val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            if (notificationManager.getNotificationChannel(channelId) == null) {
                val channel = NotificationChannel(channelId, channelName, NotificationManager.IMPORTANCE_LOW)
                notificationManager.createNotificationChannel(channel)
            }
            val notification: Notification = NotificationCompat.Builder(this, channelId)
                .setContentTitle("Call Monitoring Active")
                .setContentText("Monitoring calls in background")
                .setSmallIcon(android.R.drawable.ic_menu_call)
                .build()
            startForeground(1, notification)
        }

        // Only process when call ends (IDLE)
        val state = intent?.getStringExtra("state")
        if (state == TelephonyManager.EXTRA_STATE_IDLE) {
            processLastCall()
        }
        return START_STICKY
    }

    private fun processLastCall() {
        try {
            val resolver: ContentResolver = contentResolver
            val cursor: Cursor? = resolver.query(
                CallLog.Calls.CONTENT_URI,
                null,
                null,
                null,
                CallLog.Calls.DATE + " DESC"
            )
            if (cursor != null && cursor.moveToFirst()) {
                val callId = cursor.getLong(cursor.getColumnIndexOrThrow(CallLog.Calls._ID))
                val number = cursor.getString(cursor.getColumnIndexOrThrow(CallLog.Calls.NUMBER))
                val typeInt = cursor.getInt(cursor.getColumnIndexOrThrow(CallLog.Calls.TYPE))
                val date = cursor.getLong(cursor.getColumnIndexOrThrow(CallLog.Calls.DATE))
                val duration = cursor.getLong(cursor.getColumnIndexOrThrow(CallLog.Calls.DURATION))
                val name = cursor.getString(cursor.getColumnIndexOrThrow(CallLog.Calls.CACHED_NAME))
                val callType = when (typeInt) {
                    CallLog.Calls.INCOMING_TYPE -> "Incoming"
                    CallLog.Calls.OUTGOING_TYPE -> "Outgoing"
                    CallLog.Calls.MISSED_TYPE -> "Missed"
                    CallLog.Calls.REJECTED_TYPE -> "Rejected"
                    CallLog.Calls.BLOCKED_TYPE -> "Blocked"
                    CallLog.Calls.VOICEMAIL_TYPE -> "Voicemail"
                    else -> "Unknown"
                }
                val contactStatus = if (!name.isNullOrEmpty()) "Saved" else "Unknown"
                val dateStr = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()).format(Date(date))

                // Read timing info from SharedPreferences
                val prefs = getSharedPreferences("call_prefs", Context.MODE_PRIVATE)
                val ringingTime = prefs.getLong("ringing_time", 0L)
                val offhookTime = prefs.getLong("offhook_time", 0L)
                val idleTime = prefs.getLong("idle_time", 0L)

                // Calculate durations
                val ringDuration = if (ringingTime > 0 && offhookTime > 0) (offhookTime - ringingTime) / 1000 else 0
                val waitingTime = ringDuration // For inbound calls
                val conversationDuration = if (offhookTime > 0 && idleTime > 0) (idleTime - offhookTime) / 1000 else duration

                // Refine call_type and call_status for API
                val (apiCallType, apiCallStatus) = when (typeInt) {
                    CallLog.Calls.INCOMING_TYPE -> if (duration > 0) Pair("inbound", "Answered") else Pair("inbound", "Missed")
                    CallLog.Calls.OUTGOING_TYPE -> if (duration > 0) Pair("outbound", "Answered") else Pair("outbound", "Declined")
                    CallLog.Calls.MISSED_TYPE -> Pair("inbound", "Missed")
                    CallLog.Calls.REJECTED_TYPE -> Pair("inbound", "Declined")
                    CallLog.Calls.BLOCKED_TYPE -> Pair("inbound", "Blocked")
                    CallLog.Calls.VOICEMAIL_TYPE -> Pair("inbound", "Voicemail")
                    else -> Pair("unknown", "Unknown")
                }

                // Infer who hung up the call
                val hangupBy = when {
                    typeInt == CallLog.Calls.INCOMING_TYPE && callType == "Declined" -> "user"
                    typeInt == CallLog.Calls.OUTGOING_TYPE && duration == 0L -> "remote"
                    else -> "unknown"
                }

                val callData = mapOf(
                    "date_time" to dateStr,
                    "call_status" to apiCallStatus,
                    "caller_number" to number,
                    "call_type" to apiCallType,
                    "caller_duration" to duration.toString(),
                    "caller_waiting_time" to waitingTime.toString(),
                    "conversation_duration" to conversationDuration.toString(),
                    "ring_duration" to ringDuration.toString(),
                    "contact_status" to contactStatus,
                    "name" to (name ?: ""),
                    "hangup_by" to hangupBy
                )
                Log.d("CallService", "Full call data: $callData")
                uploadCallData(callData) {
                    // On success, update last uploaded id
                    prefs.edit().putLong("last_uploaded_call_id", callId).apply()
                }
            }
            cursor?.close()
        } catch (e: Exception) {
            Log.e("CallService", "Error reading call log: ${e.message}")
        }
    }

    private fun uploadCallData(data: Map<String, String>, onSuccess: (() -> Unit)? = null) {
        thread {
            val apiUrl = "https://www.webssphere.com/api/postdata"
            val json = JSONObject(data).toString()
            var success = false
            var attempts = 0
            while (!success && attempts < 3) {
                attempts++
                try {
                    val url = URL(apiUrl)
                    val conn = url.openConnection() as HttpURLConnection
                    conn.requestMethod = "POST"
                    conn.setRequestProperty("Content-Type", "application/json")
                    conn.doOutput = true
                    val os: OutputStream = conn.outputStream
                    os.write(json.toByteArray())
                    os.flush()
                    os.close()
                    val responseCode = conn.responseCode
                    if (responseCode in 200..299) {
                        success = true
                        Handler(Looper.getMainLooper()).post {
                            Toast.makeText(this, "Call data pushed to Callytics.", Toast.LENGTH_LONG).show()
                            Log.d("CallService", "Showing push notification after data upload.")
                            showPushNotification()
                            // showOverlayPopup() // Temporarily disabled popup
                        }
                        onSuccess?.invoke()
                    } else {
                        Log.e("CallService", "API error: $responseCode")
                        Thread.sleep(2000)
                    }
                    conn.disconnect()
                } catch (e: Exception) {
                    Log.e("CallService", "Upload error: ${e.message}")
                    Thread.sleep(2000)
                }
            }
            if (!success) {
                savePendingUpload(json)
                scheduleUploadWorker()
                Handler(Looper.getMainLooper()).post {
                    Toast.makeText(this, "No internet. Will retry upload automatically.", Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    private fun savePendingUpload(json: String) {
        val prefs = getSharedPreferences("pending_uploads", Context.MODE_PRIVATE)
        val set = prefs.getStringSet("queue", mutableSetOf())?.toMutableSet() ?: mutableSetOf()
        set.add(json)
        prefs.edit().putStringSet("queue", set).apply()
    }

    private fun loadPendingUploads(): MutableSet<String> {
        val prefs = getSharedPreferences("pending_uploads", Context.MODE_PRIVATE)
        return prefs.getStringSet("queue", mutableSetOf())?.toMutableSet() ?: mutableSetOf()
    }

    private fun clearPendingUploads() {
        val prefs = getSharedPreferences("pending_uploads", Context.MODE_PRIVATE)
        prefs.edit().remove("queue").apply()
    }

    private fun scheduleUploadWorker() {
        val workRequest = OneTimeWorkRequestBuilder<UploadWorker>()
            .setConstraints(
                Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build()
            )
            .build()
        WorkManager.getInstance(this).enqueue(workRequest)
    }

    private fun showPushNotification() {
        val channelId = "call_data_push_channel"
        val channelName = "Callytics Data Push"
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            if (notificationManager.getNotificationChannel(channelId) == null) {
                val channel = NotificationChannel(channelId, channelName, NotificationManager.IMPORTANCE_HIGH)
                channel.enableVibration(true)
                channel.enableLights(true)
                notificationManager.createNotificationChannel(channel)
            }
        }
        val notification = NotificationCompat.Builder(this, channelId)
            .setContentTitle("Callytics")
            .setContentText("Call data was pushed successfully.")
            .setSmallIcon(android.R.drawable.ic_menu_call)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()
        val notificationId = (System.currentTimeMillis() % Int.MAX_VALUE).toInt()
        notificationManager.notify(notificationId, notification)
    }

    private fun showOverlayPopup() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M && !Settings.canDrawOverlays(this)) {
            val intent = Intent(Settings.ACTION_MANAGE_OVERLAY_PERMISSION)
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            startActivity(intent)
            Toast.makeText(this, "Please grant overlay permission for popups.", Toast.LENGTH_LONG).show()
            return
        }
        Handler(Looper.getMainLooper()).post {
            val windowManager = getSystemService(Context.WINDOW_SERVICE) as WindowManager
            val inflater = getSystemService(Context.LAYOUT_INFLATER_SERVICE) as LayoutInflater
            val popupView = inflater.inflate(R.layout.popup_data_pushed, null)
            val okButton = popupView.findViewById<Button>(R.id.popup_ok)
            popupView.setOnClickListener { windowManager.removeView(popupView) }
            okButton.setOnClickListener { windowManager.removeView(popupView) }
            val params = WindowManager.LayoutParams(
                WindowManager.LayoutParams.WRAP_CONTENT,
                WindowManager.LayoutParams.WRAP_CONTENT,
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O)
                    WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY
                else
                    WindowManager.LayoutParams.TYPE_PHONE,
                WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or WindowManager.LayoutParams.FLAG_LAYOUT_IN_SCREEN,
                PixelFormat.TRANSLUCENT
            )
            params.gravity = android.view.Gravity.CENTER
            windowManager.addView(popupView, params)
            Handler(Looper.getMainLooper()).postDelayed({
                try { windowManager.removeView(popupView) } catch (_: Exception) {}
            }, 3000)
        }
    }
} 