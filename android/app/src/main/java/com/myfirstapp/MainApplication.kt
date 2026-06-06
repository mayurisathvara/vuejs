package com.myfirstapp

import android.app.Application
import com.facebook.react.PackageList
import com.facebook.react.ReactApplication
import com.facebook.react.ReactHost
import com.facebook.react.ReactNativeApplicationEntryPoint.loadReactNative
import com.facebook.react.ReactNativeHost
import com.facebook.react.ReactPackage
import com.facebook.react.defaults.DefaultReactHost.getDefaultReactHost
import com.facebook.react.defaults.DefaultReactNativeHost

class MainApplication : Application(), ReactApplication {

  override val reactNativeHost: ReactNativeHost =
      object : DefaultReactNativeHost(this) {
        override fun getPackages(): List<ReactPackage> =
            PackageList(this).packages.apply {
              // Packages that cannot be autolinked yet can be added manually here, for example:
              // add(MyReactNativePackage())
              add(object : ReactPackage {
                  override fun createNativeModules(reactContext: com.facebook.react.bridge.ReactApplicationContext) =
                      listOf(CallModule(reactContext))
                  override fun createViewManagers(reactContext: com.facebook.react.bridge.ReactApplicationContext) =
                      emptyList<com.facebook.react.uimanager.ViewManager<*, *>>()
              })
            }

        override fun getJSMainModuleName(): String = "index"

        override fun getUseDeveloperSupport(): Boolean = BuildConfig.DEBUG

        override val isNewArchEnabled: Boolean = BuildConfig.IS_NEW_ARCHITECTURE_ENABLED
        override val isHermesEnabled: Boolean = BuildConfig.IS_HERMES_ENABLED
      }

  override val reactHost: ReactHost
    get() = getDefaultReactHost(applicationContext, reactNativeHost)

  override fun onCreate() {
    super.onCreate()
    loadReactNative(this)
    
    // Schedule periodic sync via AlarmManager (works even when app is killed)
    // Set alignToClock = true for clock-aligned intervals (:00, :15, :30, :45)
    // Set alignToClock = false for 15 minutes from now
    PeriodicSyncReceiver.schedule(this, alignToClock = true)
  }
}
