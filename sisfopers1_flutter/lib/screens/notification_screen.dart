import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/data_provider.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final data = Provider.of<DataProvider>(context, listen: false);
      data.fetchNotifications().then((_) {
        data.markNotificationsRead();
      });
    });
  }

  Color _getBadgeColor(String iconKey) {
    switch (iconKey) {
      case 'broadcast':
      case 'success':
        return const Color(0xff10B981);
      case 'password':
        return const Color(0xffF59E0B);
      case 'profile':
        return const Color(0xff3B82F6);
      case 'job':
        return const Color(0xff6366F1);
      case 'mfa':
        return const Color(0xffEF4444);
      default:
        return const Color(0xff64748B);
    }
  }

  IconData _getIconData(String iconKey) {
    switch (iconKey) {
      case 'broadcast':
        return Icons.campaign_outlined;
      case 'success':
        return Icons.check_circle_outline;
      case 'password':
        return Icons.vpn_key_outlined;
      case 'profile':
        return Icons.person_outline;
      case 'job':
        return Icons.business_center_outlined;
      case 'mfa':
        return Icons.security_outlined;
      default:
        return Icons.notifications_none_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    final data = Provider.of<DataProvider>(context);

    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(60),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            border: Border(
              bottom: BorderSide(
                color: const Color(0xff0F172A).withOpacity(0.06),
                width: 1.5,
              ),
            ),
          ),
          child: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            iconTheme: const IconThemeData(color: Color(0xff0F172A)),
            title: Text(
              'Kotak Masuk Notifikasi',
              style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
            ),
          ),
        ),
      ),
      body: data.isLoading && data.notifications.isEmpty
          ? const Center(child: CircularProgressIndicator(color: Color(0xff1E3A8A)))
          : RefreshIndicator(
              onRefresh: () => data.fetchNotifications(),
              child: data.notifications.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.notifications_off_outlined, size: 48, color: Color(0xff94A3B8)),
                          const SizedBox(height: 12),
                          Text(
                            'Tidak ada notifikasi saat ini.',
                            style: GoogleFonts.outfit(color: const Color(0xff64748B), fontSize: 14),
                          ),
                        ],
                      ),
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.all(20.0),
                      itemCount: data.notifications.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (context, index) {
                        final item = data.notifications[index];
                        final badgeColor = _getBadgeColor(item.icon);
                        final iconData = _getIconData(item.icon);

                        return Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xffE2E8F0)),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.01),
                                blurRadius: 8,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: badgeColor.withOpacity(0.08),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(iconData, color: badgeColor, size: 20),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      item.title,
                                      style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      item.message,
                                      style: const TextStyle(fontSize: 12, color: Color(0xff475569), height: 1.4),
                                    ),
                                    const SizedBox(height: 10),
                                    Row(
                                      children: [
                                        const Icon(Icons.access_time, size: 12, color: Color(0xff94A3B8)),
                                        const SizedBox(width: 4),
                                        Text(
                                          item.createdAt,
                                          style: const TextStyle(fontSize: 10, color: Color(0xff94A3B8)),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
