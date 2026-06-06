package com.myfirstapp

import android.content.Context
import androidx.work.Worker
import androidx.work.WorkerParameters
import org.json.JSONObject
import java.io.OutputStream
import java.net.HttpURLConnection
import java.net.URL

class UploadWorker(appContext: Context, workerParams: WorkerParameters) : Worker(appContext, workerParams) {
    override fun doWork(): Result {
        val prefs = applicationContext.getSharedPreferences("pending_uploads", Context.MODE_PRIVATE)
        val set = prefs.getStringSet("queue", mutableSetOf())?.toMutableSet() ?: mutableSetOf()
        val iterator = set.iterator()
        val apiUrl = "https://www.webssphere.com/api/postdata"
        var allSuccess = true
        while (iterator.hasNext()) {
            val json = iterator.next()
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
                conn.disconnect()
                if (responseCode in 200..299) {
                    iterator.remove()
                } else {
                    allSuccess = false
                }
            } catch (e: Exception) {
                allSuccess = false
            }
        }
        prefs.edit().putStringSet("queue", set).apply()
        return if (allSuccess) Result.success() else Result.retry()
    }
} 