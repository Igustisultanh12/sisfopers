<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectMobileDeviceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set dynamic app.url to prevent cross-origin CORS errors when using m. subdomain
        config(['app.url' => $request->getSchemeAndHttpHost()]);

        $host = $request->getHost();
        $userAgent = $request->header('User-Agent');

        // Deteksi apakah pengguna mengakses melalui HP
        $isMobile = $this->isMobileDevice($userAgent);

        // Periksa apakah host saat ini diawali subdomain 'm.'
        $startsWithM = str_starts_with($host, 'm.');

        // 1. Jika via HP dan belum menggunakan subdomain 'm.', alihkan ke 'm.[domain]'
        if ($isMobile && !$startsWithM) {
            // Jangan redirect jika request adalah AJAX/Inertia untuk menghindari CORS preflight block
            if ($request->ajax() || $request->wantsJson() || $request->hasHeader('X-Inertia')) {
                return $next($request);
            }

            $mobileHost = 'm.' . $host;
            
            // Pertahankan skema protokol (HTTP / HTTPS)
            $scheme = $request->isSecure() ? 'https' : 'http';
            
            return redirect()->away($scheme . '://' . $mobileHost . $request->getRequestUri());
        }

        // 2. Jika via Desktop tapi memakai subdomain 'm.', kembalikan ke domain utama '[domain]'
        if (!$isMobile && $startsWithM) {
            // Jangan redirect jika request adalah AJAX/Inertia untuk menghindari CORS preflight block
            if ($request->ajax() || $request->wantsJson() || $request->hasHeader('X-Inertia')) {
                return $next($request);
            }

            // Hapus awalan 'm.' dari host
            $desktopHost = substr($host, 2);
            
            $scheme = $request->isSecure() ? 'https' : 'http';
            
            return redirect()->away($scheme . '://' . $desktopHost . $request->getRequestUri());
        }

        return $next($request);
    }

    /**
     * Mendeteksi perangkat seluler berdasarkan User Agent string
     */
    private function isMobileDevice(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|pocket pc|pps|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)
            || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|m\-solid|m50\/|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|inc)|py(02|top)|p265|pn\-2|qi(21|default_api:ask_permission|default_api:ask_question|default_api:command_status|default_api:define_subagent|default_api:generate_image|default_api:grep_search|default_api:invoke_subagent|default_api:list_dir|default_api:list_permissions|default_api:manage_subagents|default_api:manage_task|default_api:multi_replace_file_content|default_api:read_url_content|default_api:replace_file_content|default_api:run_command|default_api:schedule|default_api:search_web|default_api:send_message|default_api:view_file|default_api:write_to_file)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(id|b1|de|up)|link|smart|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));
    }
}
