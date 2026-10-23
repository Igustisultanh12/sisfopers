package id.my.sisfoperskc.app

import android.app.Application
import coil.ImageLoader
import coil.ImageLoaderFactory
import coil.disk.DiskCache
import coil.memory.MemoryCache
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.push.ForegroundWatcher
import id.my.sisfoperskc.app.push.NotificationSyncWorker
import id.my.sisfoperskc.app.push.Notifier
import id.my.sisfoperskc.app.push.PushManager
import id.my.sisfoperskc.app.update.AppUpdater

class App : Application(), ImageLoaderFactory {
    override fun onCreate() {
        super.onCreate()
        Session.init(this)
        Api.init(this)
        Notifier.createChannels(this)
        PushManager.initFromCache(this)
        ForegroundWatcher.install(this)
        if (Session.isLoggedIn) NotificationSyncWorker.schedule(this)
        AppUpdater.schedule(this)
    }

    override fun newImageLoader(): ImageLoader = ImageLoader.Builder(this)
        .okHttpClient(Api.client)
        .crossfade(true)
        .respectCacheHeaders(false)
        .memoryCache { MemoryCache.Builder(this).maxSizePercent(0.2).build() }
        .diskCache { DiskCache.Builder().directory(cacheDir.resolve("images")).maxSizeBytes(80L * 1024 * 1024).build() }
        .build()
}
