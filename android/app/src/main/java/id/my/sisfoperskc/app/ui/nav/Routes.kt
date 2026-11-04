package id.my.sisfoperskc.app.ui.nav

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AdminPanelSettings
import androidx.compose.material.icons.outlined.Assessment
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.ConfirmationNumber
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.Dns
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.FactCheck
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.History
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.ManageAccounts
import androidx.compose.material.icons.outlined.Mail
import androidx.compose.material.icons.outlined.NotificationsNone
import androidx.compose.material.icons.outlined.Password
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Settings
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material.icons.outlined.TaskAlt
import androidx.compose.material.icons.outlined.Timeline
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material.icons.outlined.VideoCall
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material.icons.outlined.Sms
import androidx.compose.material.icons.outlined.Fingerprint
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import id.my.sisfoperskc.app.data.Me
import id.my.sisfoperskc.app.ui.theme.Sx

object Rt {
    const val SPLASH = "splash"
    const val ONBOARD = "onboarding"
    const val LOGIN = "login"
    const val REGISTER = "register"
    const val SKEP_REQUEST = "skep-request"
    const val FORGOT_NIKC = "forgot-nikc"
    const val RESET_PASSWORD = "reset-password"
    const val MAINTENANCE = "maintenance"
    const val GATE_OTP = "gate/otp"
    const val GATE_SINYALMEN = "gate/sinyalmen"
    const val GATE_WILAYAH = "gate/kewilayahan"

    const val HOME = "home"
    const val NOTIF = "notifications"
    const val ACCOUNT = "account"
    const val ABOUT = "about"
    const val MENU = "menu"

    const val VERIF = "admin/verifikasi"
    const val VERIF_DETAIL = "admin/verifikasi/{uuid}"
    const val PERSONEL = "admin/personel"
    const val PERSONEL_NEW = "admin/personel/new"
    const val PERSONEL_EDIT = "admin/personel/{uuid}/edit"
    const val PERSONEL_EDU = "admin/personel/{uuid}/pendidikan"
    const val SKEP = "admin/skep"
    const val PENGKINIAN = "admin/pengkinian"
    const val EDU_VERIF = "admin/pendidikan"
    const val EKTA = "admin/ekta"
    const val EKTA_DETAIL = "admin/ekta/{id}"
    const val OTP = "admin/otp"
    const val OTP_RESET = "admin/otp-reset"
    const val BROADCAST = "broadcast"
    const val BROADCAST_NEW = "broadcast/new"
    const val BROADCAST_DETAIL = "broadcast/{uuid}"
    const val BROADCAST_EDIT = "broadcast/{uuid}/edit"
    const val FORMS = "forms"
    const val FORM_NEW = "forms/new"
    const val FORM_EDIT = "forms/{uuid}/edit"
    const val FORM_RESPONSES = "forms/{uuid}/responses"
    const val CHAT = "chat"
    const val CHAT_ROOM = "chat/{uuid}"
    const val VICON = "vicon"
    const val TICKETS = "tickets"
    const val ACTIVITY = "monitoring/activity"
    const val LOGINS = "monitoring/login"
    const val WA_LOGS = "monitoring/whatsapp"
    const val REPORTS = "reports"
    const val SETTINGS = "settings"
    const val PJU_MGMT = "pju-management"
    const val MAIL = "mail"
    const val PJU_PERSONEL = "pju/personel"

    const val MY_BROADCAST = "my/broadcast"
    const val MY_BROADCAST_DETAIL = "my/broadcast/{uuid}"
    const val MY_EDU = "my/pendidikan"
    const val MY_JOB = "my/pekerjaan"
    const val MY_PENGKINIAN = "my/pengkinian"
    const val MY_TICKETS = "my/tickets"
    const val MY_FORMS = "my/forms"
    const val MY_FORM = "my/forms/{uuid}"
    const val MY_EKTA = "my/ekta"
    const val MY_CHAT = "my/chat"

    fun with(route: String, vararg args: Pair<String, Any>): String {
        var r = route
        args.forEach { (k, v) -> r = r.replace("{$k}", android.net.Uri.encode(v.toString())) }
        return r
    }

    val topLevel = setOf(HOME, NOTIF, ACCOUNT, VERIF, CHAT, MY_CHAT, MY_BROADCAST, PERSONEL, PJU_PERSONEL, MENU)

    /** Petakan URL web (dari notifikasi) ke rute aplikasi. */
    fun fromWebUrl(url: String?, me: Me?): String {
        if (url.isNullOrBlank() || me == null) return NOTIF
        val p = url.substringBefore('?').lowercase().let { u ->
            if (u.contains("://")) "/" + u.substringAfter("://").substringAfter('/', "") else u
        }
        val personelSide = p.startsWith("/personel") || me.isPersonel
        val uuid = Regex("[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}").find(p)?.value
        return when {
            p.contains("live-chat") -> if (me.isChatOperator && !p.startsWith("/personel")) CHAT else MY_CHAT
            p.contains("vicon") -> VICON
            p.contains("verifikasi-pengkinian") || (p.contains("pengkinian") && !personelSide) -> PENGKINIAN
            p.contains("pengkinian") -> MY_PENGKINIAN
            p.contains("verifikasi-pendidikan") -> EDU_VERIF
            p.contains("/admin/verifikasi") -> if (uuid != null) with(VERIF_DETAIL, "uuid" to uuid) else VERIF
            p.contains("broadcast") && personelSide -> if (uuid != null) with(MY_BROADCAST_DETAIL, "uuid" to uuid) else MY_BROADCAST
            p.contains("broadcast") -> if (uuid != null && me.isAdmin) with(BROADCAST_DETAIL, "uuid" to uuid) else BROADCAST
            p.contains("formulir") && personelSide -> if (uuid != null) with(MY_FORM, "uuid" to uuid) else MY_FORMS
            p.contains("formulir") -> if (uuid != null && p.contains("responses")) with(FORM_RESPONSES, "uuid" to uuid) else FORMS
            p.contains("tickets") -> if (personelSide) MY_TICKETS else TICKETS
            p.contains("pendidikan") -> if (personelSide) MY_EDU else EDU_VERIF
            p.contains("pekerjaan") -> MY_JOB
            p.contains("ekta") -> if (personelSide) MY_EKTA else EKTA
            p.contains("skep") -> SKEP
            p.contains("personel") && !personelSide -> PERSONEL
            p.contains("dashboard") -> HOME
            else -> NOTIF
        }
    }
}

data class MenuItem(val route: String, val label: String, val icon: ImageVector, val color: Color = Sx.Blue, val desc: String? = null)
data class MenuGroup(val title: String, val items: List<MenuItem>)

object Menus {
    fun groups(me: Me): List<MenuGroup> {
        val selfService = MenuGroup(
            "Data Mandiri Saya",
            listOf(
                MenuItem(Rt.MY_PENGKINIAN, "Pengkinian Data", Icons.Outlined.UploadFile, Sx.Cyan, "Ajukan pembaruan status"),
                MenuItem(Rt.MY_EDU, "Pendidikan Saya", Icons.Outlined.School, Sx.Purple, "Riwayat pendidikan"),
                MenuItem(Rt.MY_JOB, "Pekerjaan Saya", Icons.Outlined.Work, Sx.Amber, "Riwayat pekerjaan"),
                MenuItem(Rt.MY_BROADCAST, "Kegiatan & Absensi Saya", Icons.Outlined.TaskAlt, Sx.Green, "Konfirmasi kehadiran"),
            ),
        )
        return when (me.roleGroup) {
            "admin" -> listOf(
                MenuGroup("Utama", listOf(MenuItem(Rt.HOME, "Dashboard", Icons.Outlined.Home))),
                MenuGroup(
                    "Kelola Personel",
                    listOf(
                        MenuItem(Rt.VERIF, "Konfirmasi Pendaftaran", Icons.Outlined.VerifiedUser, Sx.Green, "Verifikasi berkas pendaftar"),
                        MenuItem(Rt.PERSONEL, "Master Personel", Icons.Outlined.Groups, Sx.Blue, "Data seluruh personel"),
                        MenuItem(Rt.SKEP, "Manajemen SKEP", Icons.Outlined.Description, Sx.Indigo, "Database & pengajuan SKEP"),
                        MenuItem(Rt.PENGKINIAN, "Verifikasi Pengkinian Data", Icons.Outlined.FactCheck, Sx.Cyan, "Pengajuan perubahan status"),
                        MenuItem(Rt.EDU_VERIF, "Verifikasi Pendidikan", Icons.Outlined.School, Sx.Purple, "Validasi ijazah & sertifikat"),
                        MenuItem(Rt.EKTA, "E-KTA Personel", Icons.Outlined.Badge, Sx.Amber, "Penerbitan kartu anggota"),
                    ),
                ),
                MenuGroup(
                    "Otorisasi & Broadcast",
                    listOf(
                        MenuItem(Rt.OTP, "OTP Manual", Icons.Outlined.Fingerprint, Sx.Red, "Kode verifikasi akun"),
                        MenuItem(Rt.OTP_RESET, "OTP Reset Password", Icons.Outlined.Password, Sx.Amber, "Kode reset kata sandi"),
                        MenuItem(Rt.BROADCAST, "Broadcast Kegiatan", Icons.Outlined.Campaign, Sx.Blue, "Instruksi & kegiatan"),
                        MenuItem(Rt.FORMS, "Formulir & Rekrutmen", Icons.Outlined.EditNote, Sx.Green, "Formulir dinamis"),
                        MenuItem(Rt.CHAT, "Pusat Layanan Informasi", Icons.Outlined.SupportAgent, Sx.Cyan, "Live chat & panggilan"),
                        MenuItem(Rt.VICON, "Vicon & Rapat Dinas", Icons.Outlined.VideoCall, Sx.Purple, "Rapat daring"),
                        MenuItem(Rt.TICKETS, "Pengaduan & Tiket", Icons.Outlined.ConfirmationNumber, Sx.Red, "Tiket pengaduan"),
                    ),
                ),
                MenuGroup(
                    "Laporan & Auditing",
                    listOf(
                        MenuItem(Rt.ACTIVITY, "Aktivitas Sistem", Icons.Outlined.Timeline, Sx.Indigo, "Audit trail"),
                        MenuItem(Rt.LOGINS, "Log Login & Sesi", Icons.Outlined.History, Sx.Blue, "Riwayat masuk"),
                        MenuItem(Rt.WA_LOGS, "Log WhatsApp", Icons.Outlined.Sms, Sx.Green, "Riwayat pesan WA"),
                        MenuItem(Rt.REPORTS, "Laporan Pelaporan", Icons.Outlined.Assessment, Sx.Amber, "Unduh PDF & Excel"),
                    ),
                ),
                MenuGroup(
                    "Sistem & Konfigurasi",
                    listOf(
                        MenuItem(Rt.SETTINGS, "Pengaturan Sistem", Icons.Outlined.Settings, Sx.Ink2, "Konfigurasi & notifikasi push"),
                        MenuItem(Rt.PJU_MGMT, "Manajemen Akun PJU", Icons.Outlined.ManageAccounts, Sx.Blue, "Akun pejabat"),
                        MenuItem(Rt.MAIL, "Mail Gateway", Icons.Outlined.Mail, Sx.Red, "SMTP & OTP email"),
                    ),
                ),
                selfService,
            )
            "kordinator" -> listOf(
                MenuGroup("Utama", listOf(MenuItem(Rt.HOME, "Dashboard Pembinaan", Icons.Outlined.Home))),
                MenuGroup(
                    "Verifikasi & Kelola Jajaran",
                    listOf(
                        MenuItem(Rt.PERSONEL, "Master Personel Jajaran", Icons.Outlined.Groups, Sx.Blue, "Personel binaan"),
                        MenuItem(Rt.VERIF, "Konfirmasi Pendaftaran", Icons.Outlined.VerifiedUser, Sx.Green, "Verifikasi pendaftar"),
                        MenuItem(Rt.PENGKINIAN, "Verifikasi Pengkinian Data", Icons.Outlined.FactCheck, Sx.Cyan, "Pengajuan perubahan"),
                        MenuItem(Rt.EDU_VERIF, "Verifikasi Pendidikan", Icons.Outlined.School, Sx.Purple, "Validasi ijazah"),
                    ),
                ),
                MenuGroup(
                    "Broadcast & Pelaporan",
                    listOf(
                        MenuItem(Rt.BROADCAST, if (me.role == "kordinator_matra") "Broadcast Matra" else "Broadcast Angkatan", Icons.Outlined.Campaign, Sx.Blue, "Instruksi jajaran"),
                        MenuItem(Rt.FORMS, "Formulir & Rekrutmen", Icons.Outlined.EditNote, Sx.Green, "Formulir dinamis"),
                        MenuItem(Rt.CHAT, "Pusat Layanan Informasi", Icons.Outlined.SupportAgent, Sx.Cyan, "Live chat"),
                        MenuItem(Rt.VICON, "Vicon & Rapat Dinas", Icons.Outlined.VideoCall, Sx.Purple, "Rapat daring"),
                        MenuItem(Rt.REPORTS, "Laporan Pelaporan", Icons.Outlined.Assessment, Sx.Amber, "Unduh laporan"),
                        MenuItem(Rt.TICKETS, "Pengaduan & Tiket Jajaran", Icons.Outlined.ConfirmationNumber, Sx.Red, "Tiket pengaduan"),
                    ),
                ),
                MenuGroup("Data Mandiri Saya", selfService.items.filter { it.route != Rt.MY_PENGKINIAN }),
            )
            "pju" -> listOf(
                MenuGroup(
                    "Menu PJU",
                    listOfNotNull(
                        MenuItem(Rt.HOME, "Dashboard PJU", Icons.Outlined.Home),
                        MenuItem(Rt.PJU_PERSONEL, "Master Personel Jajaran", Icons.Outlined.Groups, Sx.Blue, "Personel jajaran"),
                        MenuItem(Rt.REPORTS, "Laporan Pelaporan", Icons.Outlined.Assessment, Sx.Amber, "Unduh laporan"),
                        MenuItem(Rt.BROADCAST, "Broadcast Kegiatan", Icons.Outlined.Campaign, Sx.Blue, "Kegiatan jajaran"),
                        MenuItem(Rt.FORMS, "Formulir & Rekrutmen", Icons.Outlined.EditNote, Sx.Green, "Formulir dinamis"),
                        MenuItem(Rt.CHAT, "Pusat Layanan Informasi", Icons.Outlined.SupportAgent, Sx.Cyan, "Live chat"),
                        MenuItem(Rt.VICON, "Vicon & Rapat Dinas", Icons.Outlined.VideoCall, Sx.Purple, "Rapat daring"),
                    ),
                ),
            )
            "komandan" -> listOf(
                MenuGroup("Utama", listOf(MenuItem(Rt.HOME, "Dashboard Utama", Icons.Outlined.Home))),
                selfService.copy(items = selfService.items.filter { it.route != Rt.MY_PENGKINIAN }),
            )
            else -> listOf(
                MenuGroup(
                    "Menu Personel",
                    listOf(
                        MenuItem(Rt.HOME, "Dashboard Saya", Icons.Outlined.Home),
                        MenuItem(Rt.MY_EDU, "Riwayat Pendidikan", Icons.Outlined.School, Sx.Purple, "Ijazah & sertifikat"),
                        MenuItem(Rt.MY_JOB, "Riwayat Pekerjaan", Icons.Outlined.Work, Sx.Amber, "Pekerjaan saat ini"),
                        MenuItem(Rt.MY_BROADCAST, "Tugas & Broadcast Kegiatan", Icons.Outlined.Campaign, Sx.Blue, "Konfirmasi kehadiran"),
                        MenuItem(Rt.MY_FORMS, "Formulir & Rekrutmen", Icons.Outlined.EditNote, Sx.Green, "Isi formulir"),
                        MenuItem(Rt.MY_TICKETS, "Open Tiket Pengaduan", Icons.Outlined.ConfirmationNumber, Sx.Red, "Ajukan perubahan data"),
                        MenuItem(Rt.MY_PENGKINIAN, "Pengkinian Data", Icons.Outlined.UploadFile, Sx.Cyan, "Pembaruan status"),
                        MenuItem(Rt.MY_EKTA, "E-KTA Saya", Icons.Outlined.Badge, Sx.Amber, "Kartu anggota digital"),
                        MenuItem(Rt.MY_CHAT, "Pusat Layanan Informasi", Icons.Outlined.SupportAgent, Sx.Cyan, "Tanya petugas"),
                        MenuItem(Rt.VICON, "Vicon Dinas", Icons.Outlined.VideoCall, Sx.Purple, "Rapat daring"),
                    ),
                ),
            )
        }
    }

    data class BottomItem(val route: String, val label: String, val icon: ImageVector)

    fun bottom(me: Me): List<BottomItem> {
        val home = BottomItem(Rt.HOME, "Beranda", Icons.Outlined.Home)
        val notif = BottomItem(Rt.NOTIF, "Notifikasi", Icons.Outlined.NotificationsNone)
        val account = BottomItem(Rt.ACCOUNT, "Akun", Icons.Outlined.Person)
        return when (me.roleGroup) {
            "admin" -> listOf(home, BottomItem(Rt.VERIF, "Verifikasi", Icons.Outlined.VerifiedUser), BottomItem(Rt.CHAT, "Layanan", Icons.Outlined.SupportAgent), notif, account)
            "kordinator" -> listOf(home, BottomItem(Rt.PERSONEL, "Jajaran", Icons.Outlined.Groups), BottomItem(Rt.CHAT, "Layanan", Icons.Outlined.SupportAgent), notif, account)
            "pju" -> listOf(home, BottomItem(Rt.PJU_PERSONEL, "Personel", Icons.Outlined.Groups), BottomItem(Rt.CHAT, "Layanan", Icons.Outlined.SupportAgent), notif, account)
            "komandan" -> listOf(home, BottomItem(Rt.MENU, "Menu", Icons.Outlined.Dns), notif, account)
            else -> listOf(home, BottomItem(Rt.MY_BROADCAST, "Kegiatan", Icons.Outlined.Campaign), BottomItem(Rt.MY_CHAT, "Layanan", Icons.Outlined.SupportAgent), notif, account)
        }
    }

    val adminIcon = Icons.Outlined.AdminPanelSettings
}

