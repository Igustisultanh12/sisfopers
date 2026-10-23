#!/usr/bin/env bash
# Memotret layar aplikasi (build demo) di emulator untuk panduan & paparan.
set -u
PKG=id.my.sisfoperskc.app.debug
ACT=id.my.sisfoperskc.app.MainActivity
OUT=${1:-shots}
mkdir -p "$OUT"

adb shell settings put global sysui_demo_allowed 1
adb shell am broadcast -a com.android.systemui.demo -e command enter >/dev/null
adb shell am broadcast -a com.android.systemui.demo -e command clock -e hhmm 0900 >/dev/null
adb shell am broadcast -a com.android.systemui.demo -e command battery -e level 100 -e plugged false >/dev/null
adb shell am broadcast -a com.android.systemui.demo -e command network -e wifi show -e level 4 >/dev/null
adb shell am broadcast -a com.android.systemui.demo -e command network -e mobile show -e datatype none -e level 4 >/dev/null
adb shell am broadcast -a com.android.systemui.demo -e command notifications -e visible false >/dev/null
adb shell pm grant $PKG android.permission.POST_NOTIFICATIONS 2>/dev/null || true
adb shell pm grant $PKG android.permission.CAMERA 2>/dev/null || true
adb shell pm grant $PKG android.permission.RECORD_AUDIO 2>/dev/null || true
adb shell settings put global window_animation_scale 1
adb shell settings put global transition_animation_scale 1

shot() { # nama peran rute jeda [aksi]
  local name=$1 role=$2 route=$3 wait=${4:-6} action=${5:-}
  if [ -n "$route" ]; then
    adb shell am start -S -W -n $PKG/$ACT --es demo_role "$role" --es demo_route "$route" >/dev/null
  else
    adb shell am start -S -W -n $PKG/$ACT --es demo_role "$role" >/dev/null
  fi
  sleep "$wait"
  case "$action" in
    drawer) adb shell input swipe 2 1200 800 1200 350; sleep 2 ;;
    scroll) adb shell input swipe 540 1900 540 700 600; sleep 2 ;;
  esac
  adb exec-out screencap -p > "$OUT/$name.png"
  echo "ok $name ($(stat -c %s "$OUT/$name.png") bytes)"
}

A=screenshots/assets/demo
VERIF=$(ls $A/admin | grep -oE 'admin__verifikasi__[0-9a-f-]{36}' | head -1 | sed 's/admin__verifikasi__//')
PERS=$(ls $A/admin | grep -oE 'admin__personel__[0-9a-f-]{36}' | head -1 | sed 's/admin__personel__//')
BC=$(ls $A/admin | grep -oE 'admin__broadcast__[0-9a-f-]{36}' | head -1 | sed 's/admin__broadcast__//')
FORM=$(ls $A/admin | grep -oE 'admin__formulir__[0-9a-f-]{36}' | head -1 | sed 's/admin__formulir__//')
CHAT=$(ls $A/admin | grep -oE 'admin__live-chat__[0-9a-f-]{36}' | head -1 | sed 's/admin__live-chat__//')
PBC=$(ls $A/personel | grep -oE 'personel__broadcast__[0-9a-f-]{36}' | head -1 | sed 's/personel__broadcast__//')
PFORM=$(ls $A/personel | grep -oE 'personel__formulir__[0-9a-f-]{36}' | head -1 | sed 's/personel__formulir__//')

# Tamu
shot 00_splash fresh "" 1
shot 01_onboarding fresh "" 5
shot 02_login guest "" 5
shot 03_register guest register 5
shot 04_skep_request guest skep-request 5
shot 05_forgot_nikc guest forgot-nikc 5
shot 06_reset_password guest reset-password 5
# Admin
shot 10_admin_home admin "" 7
shot 11_admin_drawer admin "" 7 drawer
shot 12_admin_verifikasi admin admin/verifikasi
shot 13_admin_verifikasi_detail admin "admin/verifikasi/$VERIF"
shot 14_admin_personel admin admin/personel
shot 15_admin_personel_new admin admin/personel/new
shot 16_admin_personel_edit admin "admin/personel/$PERS/edit"
shot 17_admin_personel_pendidikan admin "admin/personel/$PERS/pendidikan"
shot 18_admin_skep admin admin/skep
shot 19_admin_pengkinian admin admin/pengkinian
shot 20_admin_pendidikan admin admin/pendidikan
shot 21_admin_ekta admin admin/ekta
shot 22_admin_ekta_detail admin admin/ekta/1
shot 23_admin_otp admin admin/otp
shot 24_admin_otp_reset admin admin/otp-reset
shot 25_admin_broadcast admin broadcast
shot 26_admin_broadcast_new admin broadcast/new
shot 27_admin_broadcast_detail admin "broadcast/$BC"
shot 28_admin_forms admin forms
shot 29_admin_form_new admin forms/new
shot 30_admin_form_responses admin "forms/$FORM/responses"
shot 31_admin_chat admin chat
shot 32_admin_chat_room admin "chat/$CHAT"
shot 33_admin_vicon admin vicon
shot 34_admin_tickets admin tickets
shot 35_admin_activity admin monitoring/activity
shot 36_admin_logins admin monitoring/login
shot 37_admin_reports admin reports
shot 38_admin_settings admin settings
shot 39_admin_settings_scroll admin settings 6 scroll
shot 40_admin_pju admin pju-management
shot 41_admin_mail admin mail
shot 42_admin_notifications admin notifications
shot 43_admin_account admin account
shot 44_admin_account_scroll admin account 6 scroll
# Koordinator
shot 50_kord_home kmatra "" 7
shot 51_kord_drawer kmatra "" 7 drawer
shot 52_kord_personel kmatra admin/personel
shot 53_kord_broadcast kmatra broadcast
# PJU
shot 60_pju_home pju "" 7
shot 61_pju_personel pju pju/personel
shot 62_pju_broadcast_new pju broadcast/new
# Personel
shot 70_pers_home personel "" 7
shot 71_pers_drawer personel "" 7 drawer
shot 72_pers_broadcast personel my/broadcast
shot 73_pers_broadcast_detail personel "my/broadcast/$PBC"
shot 74_pers_pendidikan personel my/pendidikan
shot 75_pers_pekerjaan personel my/pekerjaan
shot 76_pers_pengkinian personel my/pengkinian
shot 77_pers_tickets personel my/tickets
shot 78_pers_forms personel my/forms
shot 79_pers_form personel "my/forms/$PFORM"
shot 80_pers_ekta personel my/ekta
shot 81_pers_chat personel my/chat
shot 82_pers_vicon personel vicon
shot 83_pers_notifications personel notifications
shot 84_pers_account personel account
shot 85_pers_account_scroll personel account 6 scroll
shot 86_pers_gate_otp personel gate/otp
shot 87_pers_gate_sinyalmen personel gate/sinyalmen
ls -la "$OUT" | head -100
