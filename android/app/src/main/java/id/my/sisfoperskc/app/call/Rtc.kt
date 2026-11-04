package id.my.sisfoperskc.app.call

import android.content.Context
import android.util.Log
import android.view.TextureView
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateMapOf
import androidx.compose.runtime.mutableStateOf
import io.agora.rtc2.ChannelMediaOptions
import io.agora.rtc2.Constants
import io.agora.rtc2.IRtcEngineEventHandler
import io.agora.rtc2.RtcEngine
import io.agora.rtc2.RtcEngineConfig
import io.agora.rtc2.video.VideoCanvas
import io.agora.rtc2.video.VideoEncoderConfiguration

/**
 * Pembungkus Agora RTC untuk panggilan 1-1 (layanan informasi) dan rapat dinas (vicon).
 * State diekspos sebagai Compose state agar UI ikut bereaksi.
 */
class Rtc(private val context: Context, appId: String) {
    val remoteUids = mutableStateListOf<Int>()
    val remoteVideoOff = mutableStateMapOf<Int, Boolean>()
    val remoteAudioOff = mutableStateMapOf<Int, Boolean>()
    val speaking = mutableStateMapOf<Int, Boolean>()
    val joined = mutableStateOf(false)
    val connectionLost = mutableStateOf(false)
    val lastError = mutableStateOf<String?>(null)

    var onRemoteJoined: ((Int) -> Unit)? = null
    var onRemoteLeft: ((Int) -> Unit)? = null

    private val main = android.os.Handler(android.os.Looper.getMainLooper())
    private val remoteViews = HashMap<Int, TextureView>()
    private var localView: TextureView? = null

    private val handler = object : IRtcEngineEventHandler() {
        override fun onJoinChannelSuccess(channel: String?, uid: Int, elapsed: Int) {
            main.post { joined.value = true }
        }

        override fun onUserJoined(uid: Int, elapsed: Int) {
            main.post {
                if (uid !in remoteUids) remoteUids.add(uid)
                onRemoteJoined?.invoke(uid)
            }
        }

        override fun onUserOffline(uid: Int, reason: Int) {
            main.post {
                remoteUids.remove(uid)
                remoteViews.remove(uid)
                onRemoteLeft?.invoke(uid)
            }
        }

        override fun onRemoteVideoStateChanged(uid: Int, state: Int, reason: Int, elapsed: Int) {
            main.post {
                remoteVideoOff[uid] = state == Constants.REMOTE_VIDEO_STATE_STOPPED || state == Constants.REMOTE_VIDEO_STATE_FAILED
            }
        }

        override fun onRemoteAudioStateChanged(uid: Int, state: Int, reason: Int, elapsed: Int) {
            main.post { remoteAudioOff[uid] = state == Constants.REMOTE_AUDIO_STATE_STOPPED }
        }

        override fun onAudioVolumeIndication(speakers: Array<out AudioVolumeInfo>?, totalVolume: Int) {
            val list = speakers ?: return
            main.post {
                speaking.keys.toList().forEach { speaking[it] = false }
                list.forEach { if (it.volume > 25) speaking[if (it.uid == 0) LOCAL else it.uid] = true }
            }
        }

        override fun onConnectionStateChanged(state: Int, reason: Int) {
            main.post {
                connectionLost.value = state == Constants.CONNECTION_STATE_RECONNECTING || state == Constants.CONNECTION_STATE_FAILED
            }
        }

        override fun onError(err: Int) {
            Log.w("Rtc", "Agora error $err")
            main.post { lastError.value = "Kode galat media: $err" }
        }
    }

    private val engine: RtcEngine? = try {
        RtcEngine.create(RtcEngineConfig().apply {
            mContext = context.applicationContext
            mAppId = appId
            mEventHandler = handler
            mChannelProfile = Constants.CHANNEL_PROFILE_COMMUNICATION
        })
    } catch (e: Throwable) {
        Log.e("Rtc", "Gagal membuat RtcEngine", e)
        lastError.value = "Modul video tidak dapat dimulai di perangkat ini."
        null
    }

    val available: Boolean get() = engine != null

    fun join(token: String?, channel: String, uid: Int, withVideo: Boolean = true) {
        val e = engine ?: return
        e.enableAudio()
        if (withVideo) {
            e.enableVideo()
            e.setVideoEncoderConfiguration(
                VideoEncoderConfiguration(
                    VideoEncoderConfiguration.VD_640x360,
                    VideoEncoderConfiguration.FRAME_RATE.FRAME_RATE_FPS_15,
                    VideoEncoderConfiguration.STANDARD_BITRATE,
                    VideoEncoderConfiguration.ORIENTATION_MODE.ORIENTATION_MODE_ADAPTIVE,
                ),
            )
            e.startPreview()
        }
        e.enableAudioVolumeIndication(500, 3, false)
        e.setDefaultAudioRoutetoSpeakerphone(true)
        val options = ChannelMediaOptions().apply {
            clientRoleType = Constants.CLIENT_ROLE_BROADCASTER
            channelProfile = Constants.CHANNEL_PROFILE_COMMUNICATION
            publishMicrophoneTrack = true
            publishCameraTrack = withVideo
            autoSubscribeAudio = true
            autoSubscribeVideo = true
        }
        val code = e.joinChannel(token ?: "", channel, uid, options)
        if (code < 0) lastError.value = "Gagal bergabung ke kanal (kode $code)."
    }

    fun localView(): TextureView {
        localView?.let { detach(it); return it }
        val v = TextureView(context)
        engine?.setupLocalVideo(VideoCanvas(v, VideoCanvas.RENDER_MODE_HIDDEN, 0))
        localView = v
        return v
    }

    fun remoteView(uid: Int): TextureView {
        remoteViews[uid]?.let { detach(it); return it }
        val v = TextureView(context)
        engine?.setupRemoteVideo(VideoCanvas(v, VideoCanvas.RENDER_MODE_HIDDEN, uid))
        remoteViews[uid] = v
        return v
    }

    private fun detach(v: TextureView) {
        (v.parent as? android.view.ViewGroup)?.removeView(v)
    }

    fun muteMic(muted: Boolean) { engine?.muteLocalAudioStream(muted) }

    fun videoEnabled(enabled: Boolean) {
        engine?.muteLocalVideoStream(!enabled)
        engine?.enableLocalVideo(enabled)
    }

    fun switchCamera() { engine?.switchCamera() }

    fun speaker(on: Boolean) { engine?.setEnableSpeakerphone(on) }

    fun leave() {
        try {
            engine?.stopPreview()
            engine?.leaveChannel()
        } catch (_: Throwable) {
        }
        joined.value = false
    }

    fun destroy() {
        leave()
        remoteViews.clear()
        localView = null
        try {
            RtcEngine.destroy()
        } catch (_: Throwable) {
        }
    }

    companion object {
        const val LOCAL = -1
    }
}
