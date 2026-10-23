<?php

return [
    // Rute jembatan aplikasi Android (/api/m/*) tidak perlu dikirim ke JavaScript web
    'except' => ['m.*', 'mobile.*'],
];
