# Agora RTC
-keep class io.agora.** { *; }
-dontwarn io.agora.**
# Kotlinx serialization
-keepattributes *Annotation*, InnerClasses
-dontnote kotlinx.serialization.**
-keepclassmembers class kotlinx.serialization.json.** { *** Companion; }
# OkHttp
-dontwarn okhttp3.**
-dontwarn okio.**
-dontwarn org.conscrypt.**
-dontwarn org.bouncycastle.**
-dontwarn org.openjsse.**
# Firebase
-keep class com.google.firebase.** { *; }
-dontwarn com.google.firebase.**
