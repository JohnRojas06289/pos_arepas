/*!
    * Start Bootstrap - SB Admin v7.0.5 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2022 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
//
// Scripts
//

window.addEventListener('DOMContentLoaded', event => {

    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    const body = document.body;

    // ── Detectar si estamos en móvil (<992px)
    function isMobile() {
        return window.innerWidth < 992;
    }

    // ── Abrir sidebar en móvil
    function openMobileSidebar() {
        body.classList.add('sb-sidenav-toggled');
        body.classList.add('sb-sidebar-open');
        body.style.overflow = 'hidden'; // Bloquear scroll
    }

    // ── Cerrar sidebar en móvil
    function closeMobileSidebar() {
        body.classList.remove('sb-sidenav-toggled');
        body.classList.remove('sb-sidebar-open');
        body.style.overflow = '';
    }

    // ── Toggle según contexto
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (isMobile()) {
                if (body.classList.contains('sb-sidenav-toggled')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                // Desktop: comportamiento original (colapsar sidebar)
                body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', body.classList.contains('sb-sidenav-toggled'));
            }
        });
    }

    // ── En móvil: cerrar sidebar al hacer click en el overlay (::before del content)
    const sidenavContent = document.getElementById('layoutSidenav_content');
    if (sidenavContent) {
        sidenavContent.addEventListener('click', function(e) {
            if (isMobile() && body.classList.contains('sb-sidenav-toggled')) {
                closeMobileSidebar();
            }
        });
    }

    // ── En móvil: cerrar sidebar al hacer click en cualquier enlace del menú
    const sidenavNav = document.getElementById('layoutSidenav_nav');
    if (sidenavNav) {
        sidenavNav.addEventListener('click', function(e) {
            if (isMobile() && body.classList.contains('sb-sidenav-toggled')) {
                var target = e.target.closest('a[href]');
                if (target && !target.getAttribute('data-bs-toggle')) {
                    closeMobileSidebar();
                }
            }
        });
    }

    // ── Cerrar con Escape en móvil
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isMobile() && body.classList.contains('sb-sidenav-toggled')) {
            closeMobileSidebar();
        }
    });

    // ── Al redimensionar ventana: limpiar estado de móvil si se pasa a desktop
    window.addEventListener('resize', function() {
        if (!isMobile()) {
            body.classList.remove('sb-sidebar-open');
            body.style.overflow = '';
        }
    });

});
