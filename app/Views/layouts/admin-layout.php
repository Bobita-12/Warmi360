<?php
$base_url = "http://localhost/Warmi360-Refactor/public";
include __DIR__ . "/../shared/header-admin.php";

// Detectar la sección actual
$currentSection = $_GET['section'] ?? 'inicio';
?>

<!-- 🌸 Layout principal -->
<div class="flex min-h-screen bg-[#EDE7F6]">

    <!-- 🟣 SIDEBAR -->
    <aside class="bg-white w-72 rounded-tr-2xl rounded-br-2xl shadow-md p-6 flex flex-col m-4 mr-0">
        <div class="flex flex-col gap-3 flex-1">
            <?php
            $links = [
                'inicio' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75l8.485-6.364a1 1 0 011.03 0L21 9.75M4.5 10.5v9.75A1.5 1.5 0 006 21.75h12a1.5 1.5 0 001.5-1.5v-9.75" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.25 12l9.517-7.137a1.125 1.125 0 011.466 0L22.75 12M4.5 10.5v9A1.5 1.5 0 006 21h12a1.5 1.5 0 001.5-1.5v-9" /></svg>'
                    ],
                    'label' => 'Dashboard'
                ],
                'usuarias' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V4H2v16h5m10 0H7m10 0v-3a4 4 0 00-8 0v3" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8 10a4 4 0 118 0 4 4 0 01-8 0zm12 8a8 8 0 10-16 0h16z" clip-rule="evenodd"/></svg>'
                    ],
                    'label' => 'Usuarias'
                ],
                'productos' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c.341 0 .68.034 1.013.1l1.627-3.27A1 1 0 0115.54 4h4.92a.54.54 0 01.54.54v4.92a1 1 0 01-.83.986L16.17 11.9c.066.333.1.672.1 1.013 0 .341-.034.68-.1 1.013l3.999 1.454a1 1 0 01.83.986v4.92a.54.54 0 01-.54.54h-4.92a1 1 0 01-.9-.529l-1.627-3.27A4.97 4.97 0 0112 16a4.97 4.97 0 01-1.013-.1l-1.627 3.27A1 1 0 018.46 20H3.54a.54.54 0 01-.54-.54v-4.92a1 1 0 01.83-.986l3.999-1.454A4.97 4.97 0 017 13a4.97 4.97 0 01.1-1.013L3.101 10.53A1 1 0 012.27 9.54V4.62A.54.54 0 012.81 4h4.92a1 1 0 01.9.529l1.627 3.27C11.32 8.034 11.659 8 12 8z" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-5" viewBox="0 0 24 24"><path d="M5 3h14a2 2 0 012 2v2H3V5a2 2 0 012-2zm16 6H3v10a2 2 0 002 2h14a2 2 0 002-2V9z"/></svg>'
                    ],
                    'label' => 'Artículos (Tienda)'
                ],
                'planes' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2l4 -4m6 2a9 9 0 11-18 0a9 9 0 0118 0z" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-5" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zm4.03 8.78a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd"/></svg>'
                    ],
                    'label' => 'Planes'
                ],
                'biblioteca' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m0 0l-3-3m3 3l3-3m-6-9h12" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-5" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14H3V5zm6 3h6v2H9V8zm0 4h6v2H9v-2z"/></svg>'
                    ],
                    'label' => 'Biblioteca'
                ],
                'eventos' => [
                    'icon' => [
                        'outline' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
                        'solid' => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-5" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2H5zm2 7a1 1 0 100 2h10a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>'
                    ],
                    'label' => 'Eventos'
                ]
            ];

            foreach ($links as $key => $item):
                $isActive = ($currentSection === $key);
                $icon = $isActive ? $item['icon']['solid'] : $item['icon']['outline'];
            ?>
                <a href="<?= $base_url ?>/?view=admin&section=<?= $key ?>"
                   class="flex items-center gap-3 px-4 py-2 rounded-xl transition-all
                          <?= $isActive
                              ? 'bg-[#9F7AEA] text-white font-semibold shadow-md'
                              : 'text-[#5B4B8A] hover:bg-[#E9D8FD]' ?>">
                    <?= $icon ?>
                    <span class="text-[15px]"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <!-- 🧩 CONTENIDO PRINCIPAL -->
    <main class="flex-1 bg-[#EDE7F6] m-0 p-10 overflow-y-auto rounded-none">
        <?= $content ?? "<h2 class='text-gray-500'>Sin contenido disponible.</h2>"; ?>
    </main>
</div>
<!-- 🌐 Variable global accesible por todos los JS -->
<script>
    const baseUrl = "<?= $base_url ?>";
</script>

<!-- 🔹 Cargar scripts según la sección -->
<?php
switch ($currentSection) {
    case 'usuarias':
        echo "<script src='$base_url/js/admin-usuarias.js'></script>";
        break;
    case 'productos':
        echo "<script src='$base_url/js/admin-productos.js'></script>";
        break;
    case 'planes':
        echo "<script src='$base_url/js/admin-planes.js'></script>";
        break;
    case 'biblioteca':
        echo "<script src='$base_url/js/admin-biblioteca.js'></script>";
        break;
    case 'eventos':
        echo "<script src='$base_url/js/admin-eventos.js'></script>";
        break;
}
?>
</body>
</html>
