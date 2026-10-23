package id.my.sisfoperskc.app.data

import android.content.ContentValues
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.provider.MediaStore
import android.provider.OpenableColumns
import android.webkit.MimeTypeMap
import android.widget.Toast
import androidx.core.content.FileProvider
import java.io.File

object Files {
    fun describe(context: Context, uri: Uri): UploadFile {
        var name = "berkas"
        var size = -1L
        try {
            context.contentResolver.query(uri, null, null, null, null)?.use { c ->
                val ni = c.getColumnIndex(OpenableColumns.DISPLAY_NAME)
                val si = c.getColumnIndex(OpenableColumns.SIZE)
                if (c.moveToFirst()) {
                    if (ni >= 0) name = c.getString(ni) ?: name
                    if (si >= 0 && !c.isNull(si)) size = c.getLong(si)
                }
            }
        } catch (_: Exception) {
        }
        if (uri.scheme == "file") {
            uri.path?.let { File(it) }?.let { f -> name = f.name; size = f.length() }
        }
        var mime = context.contentResolver.getType(uri)
        if (mime.isNullOrBlank()) {
            val ext = name.substringAfterLast('.', "").lowercase()
            mime = MimeTypeMap.getSingleton().getMimeTypeFromExtension(ext) ?: "application/octet-stream"
        }
        if (!name.contains('.')) {
            MimeTypeMap.getSingleton().getExtensionFromMimeType(mime)?.let { name = "$name.$it" }
        }
        return UploadFile(uri, name, mime, size)
    }

    /** Uri tujuan foto dari kamera (TakePicture). */
    fun newCameraUri(context: Context): Uri {
        val dir = File(context.cacheDir, "camera").apply { mkdirs() }
        val file = File(dir, "foto_${System.currentTimeMillis()}.jpg")
        return FileProvider.getUriForFile(context, context.packageName + ".files", file)
    }

    fun humanSize(bytes: Long): String = when {
        bytes < 0 -> ""
        bytes < 1024 -> "$bytes B"
        bytes < 1024 * 1024 -> "${bytes / 1024} KB"
        else -> String.format("%.1f MB", bytes / 1024.0 / 1024.0)
    }

    /** Simpan ke folder Download publik lalu buka dengan aplikasi yang sesuai. */
    fun saveAndOpen(context: Context, d: Api.Downloaded) {
        val saved = saveToDownloads(context, d)
        if (saved) Toast.makeText(context, "Tersimpan di folder Download: ${d.name}", Toast.LENGTH_SHORT).show()
        open(context, d.file, d.mime)
    }

    fun saveToDownloads(context: Context, d: Api.Downloaded): Boolean = try {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            val values = ContentValues().apply {
                put(MediaStore.Downloads.DISPLAY_NAME, d.name)
                put(MediaStore.Downloads.MIME_TYPE, d.mime)
                put(MediaStore.Downloads.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS + "/SISFOPERSKC")
            }
            val uri = context.contentResolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values)
            if (uri != null) {
                context.contentResolver.openOutputStream(uri)?.use { out -> d.file.inputStream().use { it.copyTo(out) } }
                true
            } else false
        } else {
            @Suppress("DEPRECATION")
            val dir = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS), "SISFOPERSKC")
            dir.mkdirs()
            d.file.copyTo(File(dir, d.name), overwrite = true)
            true
        }
    } catch (_: Exception) {
        false
    }

    fun open(context: Context, file: File, mime: String) {
        try {
            val uri = FileProvider.getUriForFile(context, context.packageName + ".files", file)
            val intent = Intent(Intent.ACTION_VIEW).apply {
                setDataAndType(uri, mime)
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION or Intent.FLAG_ACTIVITY_NEW_TASK)
            }
            context.startActivity(Intent.createChooser(intent, "Buka dengan").addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
        } catch (_: Exception) {
            Toast.makeText(context, "Tidak ada aplikasi untuk membuka berkas ini.", Toast.LENGTH_LONG).show()
        }
    }

    fun share(context: Context, file: File, mime: String) {
        try {
            val uri = FileProvider.getUriForFile(context, context.packageName + ".files", file)
            val intent = Intent(Intent.ACTION_SEND).apply {
                type = mime
                putExtra(Intent.EXTRA_STREAM, uri)
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            }
            context.startActivity(Intent.createChooser(intent, "Bagikan").addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
        } catch (_: Exception) {
        }
    }
}
