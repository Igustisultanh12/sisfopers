import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'providers/auth_provider.dart';
import 'providers/data_provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';

import 'services/notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.initialize();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProxyProvider<AuthProvider, DataProvider>(
          create: (_) => DataProvider(null),
          update: (_, auth, previous) => DataProvider(auth.token),
        ),
      ],
      child: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          return MaterialApp(
            title: 'SISFOPERSKC Mobile',
            debugShowCheckedModeBanner: false,
            theme: ThemeData(
              useMaterial3: true,
              colorScheme: ColorScheme.fromSeed(
                seedColor: const Color(0xff2563EB),
                primary: const Color(0xff2563EB),
                secondary: const Color(0xff1D4ED8),
                surface: Colors.white,
                background: const Color(0xffF8FAFC),
              ),
              textTheme: GoogleFonts.outfitTextTheme(
                Theme.of(context).textTheme,
              ),
            ),
            home: auth.isAuth 
                ? const DashboardScreen() 
                : const LoginScreen(),
          );
        },
      ),
    );
  }
}
