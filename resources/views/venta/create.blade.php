@extends('layouts.app')

@section('title', 'Punto de Venta')

@push('css')
<style>
    body { overflow: hidden; }
    #layoutSidenav_content { padding-top: 60px !important; }
    .pos-container { height: calc(100vh - 60px); overflow: hidden; }
    #agente-ia-btn, #agente-ia-panel { display: none !important; }

    /* Fix for extra spacing — override pos-theme.css higher-specificity rule */
    /* padding-top:60px clears the fixed topnav; pos-container fills Y=60px→100vh */
    #layoutSidenav_content main { padding: 0 !important; margin: 0 !important; }

    /* Sidebar de categorías */
    .category-sidebar {
        height: 100%;
        overflow-y: auto;
        background: var(--bg-sidebar);
        border-right: 1px solid var(--border-sidebar);
        box-shadow: 2px 0 10px rgba(0,0,0,0.12);
        max-width: 15%;
    }

    .product-grid {
        height: 100%;
        overflow-y: auto;
        padding: 1rem;
        padding-top: 0.5rem;
        background: var(--bg-primary);
    }

    .cart-section {
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: var(--bg-card);
        border-left: 1px solid var(--border-color);
        box-shadow: -2px 0 10px rgba(0,0,0,0.04);
    }



    /* Botones de categoría más grandes y claros */
    .category-btn {
        width: 100%;
        text-align: left;
        padding: 13px 12px;
        background: transparent;
        border: none;
        border-bottom: 1px solid var(--border-sidebar);
        color: var(--text-sidebar);
        transition: all 0.25s ease;
        font-weight: 500;
        font-size: 0.92rem;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }
    
    .category-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: var(--color-accent);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .category-btn:hover, .category-btn.active {
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
        color: #fff;
        font-weight: bold;
        transform: translateX(5px);
    }

    .category-btn.active::before {
        transform: scaleY(1);
    }
    
    .category-btn i { 
        width: 30px; 
        text-align: center; 
        margin-right: 8px; 
        font-size: 1.1rem;
    }
    
    .category-btn .shortcut-hint {
        float: right;
        font-size: 0.75rem;
        opacity: 0.6;
        background: rgba(255,255,255,0.1);
        padding: 2px 6px;
        border-radius: 3px;
    }

    /* ── Category sidebar toggle ── */
    .category-sidebar-header {
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
    }
    .category-sidebar-header:hover {
        background: rgba(255,255,255,0.06);
    }
    .category-sidebar.compact {
        flex: 0 0 54px !important;
        width: 54px !important;
        max-width: 54px !important;
        min-width: 54px !important;
    }
    .category-sidebar.compact .cat-label {
        display: none;
    }
    .category-sidebar.compact .category-btn {
        text-align: center;
        padding: 10px 0;
    }
    .category-sidebar.compact .category-btn i {
        width: auto;
        margin-right: 0;
        font-size: 1rem;
    }
    .category-sidebar.compact .category-btn:hover,
    .category-sidebar.compact .category-btn.active {
        transform: none;
    }
    .category-sidebar.compact .cat-sidebar-header-inner {
        justify-content: center !important;
    }
    .category-sidebar.compact .cat-sidebar-title,
    .category-sidebar.compact .cat-toggle-icon {
        display: none;
    }
    .category-sidebar.compact .cat-header-icon {
        margin-right: 0 !important;
    }

    /* Más productos por fila cuando el sidebar está compacto */
    #productsContainer.cat-compact .product-item {
        flex: 0 0 16.6667%;
        max-width: 16.6667%;
    }

    /* Tarjetas de producto mejoradas */
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid var(--border-color);
        overflow: hidden;
        border-radius: 12px;
        background: var(--bg-card);
        box-shadow: var(--card-shadow);
    }

    .product-card:hover {
        transform: translateY(-4px) scale(1.02);
        border-color: var(--color-accent);
        box-shadow: 0 8px 16px rgba(240,199,94,0.22);
    }
    
    .product-card:active { 
        transform: translateY(-2px) scale(0.98); 
    }
    
    .product-img-container {
        height: 140px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .product-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-img {
        transform: scale(1.1);
    }
    
    .product-price {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .product-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: normal;
        word-break: break-word;
        line-height: 1.3;
    }

    /* Items del carrito */
    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0;
        background: var(--bg-primary);
    }

    .cart-item {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-card);
        margin: 6px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
    }
    
    .cart-item:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transform: translateX(4px);
    }

    .cart-item-details {
        min-width: 0;
    }

    .cart-item-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .cart-item-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .cart-item-field label {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 600;
        margin: 0;
    }

    .cart-item-field .form-control {
        min-height: 34px;
        padding: 6px 10px;
        font-size: 0.9rem;
    }

    .cart-price-input {
        width: 110px;
    }

    .cart-qty-input {
        width: 58px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    .cart-qty-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
        flex-shrink: 0;
        user-select: none;
    }
    .cart-qty-btn:hover {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: #fff;
    }
    .cart-qty-btn.minus:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .cart-item-subtotal {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 800;
        color: var(--color-primary);
    }
    
    .cart-item-new {
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .cart-footer {
        padding: 1rem 1.1rem;
        background: var(--bg-card);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
    }

    .smart-cash-btn {
        font-size: 0.88rem;
        font-weight: 700;
        padding: 10px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 2px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
    }

    .smart-cash-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        border-color: var(--color-accent);
        background-color: var(--color-accent-subtle);
    }

    /* Total más prominente */
    .total-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.2rem !important;
        font-weight: 900 !important;
        color: var(--color-success) !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }

    /* Botón de cobrar */
    #btnPay {
        font-size: 1.15rem;
        font-weight: 800;
        padding: 16px !important;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(76,175,125,0.30);
        transition: all 0.25s ease;
        border: none;
        background: linear-gradient(135deg, var(--color-success) 0%, #3a9068 100%);
        color: #fff;
    }

    #btnPay:not(:disabled):hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(76,175,125,0.40);
    }

    #btnPay:not(:disabled):active { transform: translateY(-1px); }
    #btnPay:disabled { opacity: 0.5; cursor: not-allowed; }
    
    /* Inputs mejorados */
    .form-control {
        border-radius: 8px;
        border: 1.5px solid var(--border-input);
        padding: 10px 12px;
        font-size: 1rem;
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-subtle);
    }
    
    /* Búsqueda mejorada */
    #searchInput {
        font-size: 1.1rem;
        padding: 14px 16px;
        border-radius: 12px;
    }
    
    /* Badge del carrito */
    #cartCount {
        font-size: 1rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
    }
    
    /* Scrollbar personalizada */
    ::-webkit-scrollbar { 
        width: 8px; 
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb { 
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
    }
    
    footer { display: none !important; }
    
    /* Indicador de atajo de teclado */
    .keyboard-hint {
        position: fixed;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: #fff;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .keyboard-hint.show {
        opacity: 1;
    }
    
    /* Animación de pulso para el botón de cobrar */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    
    #btnPay:not(:disabled) {
        animation: pulse 2s infinite;
    }
    
    /* Mobile: cart como panel deslizable desde abajo */
    .cart-toggle-mobile {
        display: none;
        position: fixed;
        bottom: 90px;
        left: 16px;
        z-index: 1045;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bg-sidebar);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.35);
        font-size: 1.2rem;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2px;
    }
    .cart-toggle-mobile .badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--color-accent);
        color: var(--color-secondary);
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .mobile-cart-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1039;
    }
    .mobile-cart-overlay.active { display: block; }

    /* Responsive mejorado */
    @media (max-width: 767px) {
        .cart-toggle-mobile { display: flex; }

        /* Cart panel: full-screen slide-up en móvil */
        .cart-section {
            position: fixed !important;
            bottom: 0;
            left: 0;
            right: 0;
            height: 85dvh;
            max-height: 85dvh;
            z-index: 1040;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 32px rgba(0,0,0,0.2);
        }
        .cart-section.mobile-open { transform: translateY(0); }

        /* ── Vista de lista en móvil ── */
        #productsContainer {
            flex-direction: column !important;
            --bs-gutter-x: 0 !important;
            --bs-gutter-y: 0 !important;
            margin: 0 !important;
        }
        #productsContainer > .product-item {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
            padding: 0 !important;
        }
        .product-card {
            flex-direction: row !important;
            border-radius: 0 !important;
            border: none !important;
            border-bottom: 1px solid var(--border-color) !important;
            box-shadow: none !important;
            min-height: 50px;
        }
        .product-card:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        .product-card:active {
            background: var(--color-accent-subtle) !important;
            transform: none !important;
        }
        .product-img-container { display: none !important; }
        .product-card .card-body {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 10px 12px !important;
            text-align: left !important;
            gap: 10px;
            width: 100%;
        }
        .product-name {
            font-size: 0.9rem !important;
            flex: 1 1 0 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0 !important;
        }
        .product-price {
            font-size: 0.88rem !important;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .stock-display { display: none !important; }

        /* Espacio para la alpha-bar fija */
        #productGrid { padding-right: 26px !important; }

        /* Alpha-bar flotante en móvil */
        #alpha-bar {
            display: flex !important;
            position: fixed !important;
            right: 0;
            top: 60px;
            bottom: 0;
            height: auto !important;
            width: 22px !important;
            z-index: 20;
            background: var(--bg-card);
            border-left: 1px solid var(--border-color);
            box-shadow: -2px 0 8px rgba(0,0,0,0.08);
        }

        .total-display { font-size: 2rem !important; }
        #btnPay { font-size: 1.1rem; padding: 16px !important; }
        .cart-footer { padding: 0.75rem; }
        .smart-cash-btn { min-height: 44px; }
    }


    /* ── Vista simple: sin imágenes, tarjetas más pequeñas ── */
    #productsContainer.pos-view-simple .product-img-container {
        display: none !important;
    }
    #productsContainer.pos-view-simple .product-item {
        flex: 0 0 16.666% !important;
        max-width: 16.666% !important;
    }
    #productsContainer.pos-view-simple .product-card .card-body {
        padding: 0.5rem 0.4rem !important;
        text-align: center;
    }
    #productsContainer.pos-view-simple .product-name {
        font-size: 0.78rem !important;
    }
    #productsContainer.pos-view-simple .product-price {
        font-size: 0.88rem !important;
    }
    @media (max-width: 991px) {
        #productsContainer.pos-view-simple .product-item {
            flex: 0 0 25% !important;
            max-width: 25% !important;
        }
    }
    @media (max-width: 575px) {
        #productsContainer.pos-view-simple .product-item {
            flex: 0 0 33.333% !important;
            max-width: 33.333% !important;
        }
    }

    @media (min-width: 992px) {
        .col-lg-20 {
            flex: 0 0 auto;
            width: 20%;
        }
    }

    /* ── Multi-cart tabs ── */
    #cartTabsContainer {
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex-wrap: nowrap;
    }
    #cartTabsContainer::-webkit-scrollbar { display: none; }
    .cart-tab {
        position: relative;
        width: 48px;
        height: 34px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        border: 2px solid transparent;
        color: rgba(255,255,255,0.55);
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        flex-shrink: 0;
        line-height: 1;
    }
    .cart-tab:hover {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }
    .cart-tab.active {
        background: var(--color-accent);
        border-color: var(--color-accent);
        color: var(--color-secondary);
        box-shadow: 0 2px 8px rgba(240,199,94,0.35);
    }
    .cart-tab-close {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #ef4444;
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease, transform 0.15s ease;
        z-index: 3;
        line-height: 1;
        pointer-events: none;
    }
    .cart-tab:hover .cart-tab-close {
        opacity: 1;
        pointer-events: auto;
    }
    .cart-tab-close:hover {
        transform: scale(1.2);
        background: #dc2626;
    }
    .cart-tab-dot {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        border: 1.5px solid rgba(0,0,0,0.25);
        animation: cartDotBlink 1.2s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes cartDotBlink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.25; transform: scale(0.65); }
    }
    .cart-add-btn {
        width: 30px;
        height: 34px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        border: 2px dashed rgba(255,255,255,0.35);
        color: rgba(255,255,255,0.6);
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
        flex-shrink: 0;
    }
    .cart-add-btn:not(:disabled):hover {
        background: rgba(255,255,255,0.22);
        color: #fff;
        border-color: rgba(255,255,255,0.65);
    }
    .cart-add-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* ── Fila de recomendados ── */
    .recommended-row {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
        padding: 6px 2px 8px;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex-shrink: 0;
    }
    .recommended-row::-webkit-scrollbar { display: none; }
    .recommended-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .rec-item {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1px;
        padding: 5px 9px;
        border-radius: 20px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        cursor: pointer;
        transition: all 0.18s ease;
        flex-shrink: 0;
        text-align: left;
        line-height: 1.2;
    }
    .rec-item:hover {
        border-color: var(--color-accent);
        background: var(--color-accent-subtle);
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(240,199,94,0.2);
    }
    .rec-item:active { transform: translateY(0); }
    .rec-item-name {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-primary);
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rec-item-price {
        font-size: 0.7rem;
        font-family: 'JetBrains Mono', monospace;
        color: var(--color-primary);
        font-weight: 700;
    }

    /* ── Barra alfabética ── */
    #alpha-bar {
        width: 22px;
        flex-shrink: 0;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between; /* letras distribuidas a lo alto */
        padding: 4px 0;
        background: rgba(0,0,0,0.03);
        border-left: 1px solid var(--border-color);
        user-select: none;
        touch-action: none;
        z-index: 5;
    }
    [data-theme="dark"] #alpha-bar {
        background: rgba(255,255,255,0.03);
    }
    .alpha-letter {
        font-size: 9px;
        font-weight: 700;
        line-height: 1;
        padding: 1px 2px;
        cursor: pointer;
        color: var(--text-muted);
        border-radius: 3px;
        transition: all 0.12s ease;
        width: 100%;
        text-align: center;
        flex-shrink: 0;
    }
    .alpha-letter:hover {
        background: var(--color-primary) !important;
        color: #fff !important;
    }
    .alpha-letter.active {
        background: var(--color-primary) !important;
        color: #fff !important;
    }
    .alpha-letter.has-items {
        color: var(--color-primary);
        font-weight: 800;
    }
    .alpha-letter.no-items {
        opacity: 0.25;
        cursor: default;
    }

    /* ── Pedidos pendientes ── */
    .cart-orders-btn {
        position: relative;
        width: 34px; height: 34px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(255,255,255,0.3);
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s ease; padding: 0; flex-shrink: 0;
    }
    .cart-orders-btn.has-orders {
        border-color: #f97316;
        color: #f97316;
        animation: ordersPulse 1.5s ease-in-out infinite;
    }
    @keyframes ordersPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(249,115,22,0.4); }
        50% { box-shadow: 0 0 0 6px rgba(249,115,22,0); }
    }
    .cart-orders-badge {
        position: absolute; top: -5px; right: -5px;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: #f97316; color: #fff;
        font-size: 0.6rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .orders-panel {
        position: absolute; top: 0; left: 0; right: 0;
        background: var(--bg-card);
        border-bottom: 2px solid var(--border-color);
        z-index: 50;
        max-height: 60%;
        overflow-y: auto;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        display: none;
    }
    .orders-panel.open { display: block; }
    .order-item {
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
    }
    .order-item:last-child { border-bottom: none; }

    /* ── Pago dividido ── */
    /* ── Modal pago dividido ── */
    #modalSplitPay .modal-header {
        background: linear-gradient(135deg, var(--bg-sidebar) 0%, #1e293b 100%);
        color: #fff;
        border-bottom: none;
        border-radius: 16px 16px 0 0;
        padding: 18px 24px 14px;
    }
    #modalSplitPay .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    #modalSplitPay .modal-body {
        padding: 20px 24px 8px;
        background: var(--bg-primary);
    }
    #modalSplitPay .modal-footer {
        background: var(--bg-card);
        border-top: 1px solid var(--border-color);
        padding: 14px 24px;
    }
    .split-total-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem;
        font-weight: 900;
        color: #fff;
        letter-spacing: -1px;
    }
    .split-method-card {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: var(--bg-card);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .split-method-card:focus-within {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-subtle);
    }
    .split-method-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .split-method-card-title {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-primary);
    }
    .split-method-card-input {
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 800 !important;
        font-size: 1.4rem !important;
        text-align: right;
        border: none !important;
        border-bottom: 2px solid var(--border-color) !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--text-primary) !important;
        padding: 4px 0 !important;
        width: 100%;
        outline: none !important;
        box-shadow: none !important;
    }
    .split-method-card-input:focus {
        border-bottom-color: var(--color-primary) !important;
        box-shadow: none !important;
    }
    .split-progress-bar {
        height: 8px;
        border-radius: 4px;
        background: var(--border-color);
        overflow: hidden;
        margin: 12px 0 6px;
    }
    .split-progress-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, var(--color-success), #22c55e);
        transition: width 0.3s ease;
        width: 0%;
    }
    .split-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .split-summary-row .val {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--text-primary);
    }
    .split-summary-row.pending .val { color: #ef4444; }
    .split-summary-row.ok .val { color: var(--color-success); }
    #btnConfirmSplit {
        font-size: 1rem;
        font-weight: 800;
        padding: 12px 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--color-success) 0%, #16a34a 100%);
        border: none;
        color: #fff;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(76,175,125,0.3);
    }
    #btnConfirmSplit:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(76,175,125,0.4);
    }
    #btnConfirmSplit:disabled { opacity: 0.45; cursor: not-allowed; }
    .split-remaining {
        font-size: 0.78rem;
        font-weight: 700;
        text-align: right;
    }
    .split-remaining.ok { color: var(--color-success); }
    .split-remaining.pending { color: #ef4444; }
    #btnToggleSplit {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1.5px dashed var(--border-color);
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    #btnToggleSplit:hover, #btnToggleSplit.active {
        border-color: var(--color-primary);
        color: var(--color-primary);
        background: var(--color-primary-subtle);
    }
    #btnToggleSplit.active {
        background: var(--color-primary);
        color: #fff;
        border-style: solid;
    }
</style>
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')
<form action="{{ route('ventas.store') }}" method="post" id="ventaForm" class="h-100">
    @csrf
    <div class="row g-0 pos-container">
        
        <!-- Column 1: Categories -->
        <div class="col-md-2 category-sidebar d-none d-md-block" id="categorySidebar">
            <div class="p-3 border-bottom category-sidebar-header" onclick="toggleCategorySidebar()" style="border-color:var(--border-sidebar)!important;">
                <h6 class="m-0 d-flex align-items-center cat-sidebar-header-inner" style="color:var(--text-sidebar);font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.06em;">
                    <i class="fa-solid fa-layer-group cat-header-icon me-2" style="color:var(--color-accent);"></i>
                    <span class="cat-sidebar-title">Categorías</span>
                    <i class="fa-solid fa-chevron-left cat-toggle-icon ms-auto" style="color:var(--text-muted);font-size:0.7rem;"></i>
                </h6>
            </div>
            <button type="button" class="category-btn active" title="Todo" onclick="filterCategory('all', this)">
                <i class="fa-solid fa-border-all"></i><span class="cat-label"> Todo</span>
            </button>
            @php
            $catIconMap = [
                'arepa'     => 'fa-bread-slice',
                'bebida'    => 'fa-mug-hot',
                'jugo'      => 'fa-glass-water',
                'agua'      => 'fa-droplet',
                'gaseosa'   => 'fa-bottle-water',
                'cafe'      => 'fa-mug-saucer',
                'café'      => 'fa-mug-saucer',
                'combo'     => 'fa-utensils',
                'postre'    => 'fa-cake-candles',
                'snack'     => 'fa-cookie',
                'empanada'  => 'fa-circle-half-stroke',
                'chicha'    => 'fa-wine-glass',
                'pan'       => 'fa-bread-slice',
                'sopa'      => 'fa-bowl-food',
                'caldo'     => 'fa-bowl-food',
                'bandeja'   => 'fa-plate-wheat',
                'proteina'  => 'fa-drumstick-bite',
                'proteína'  => 'fa-drumstick-bite',
                'carne'     => 'fa-drumstick-bite',
                'pollo'     => 'fa-drumstick-bite',
                'cerdo'     => 'fa-drumstick-bite',
                'fruta'     => 'fa-apple-whole',
                'ensalada'  => 'fa-leaf',
                'vegetal'   => 'fa-carrot',
                'dulce'     => 'fa-candy-cane',
                'helado'    => 'fa-ice-cream',
                'otro'      => 'fa-ellipsis',
                'varios'    => 'fa-ellipsis',
            ];
            @endphp
            @foreach ($categorias as $cat)
            @php
            $catNameLower = strtolower($cat->caracteristica->nombre);
            $catIcon = 'fa-tag';
            foreach ($catIconMap as $keyword => $icon) {
                if (str_contains($catNameLower, $keyword)) { $catIcon = $icon; break; }
            }
            @endphp
            <button type="button" class="category-btn" title="{{ $cat->caracteristica->nombre }}" onclick="filterCategory('{{$cat->id}}', this)">
                <i class="fa-solid {{ $catIcon }}"></i><span class="cat-label"> {{$cat->caracteristica->nombre}}</span>
            </button>
            @endforeach
        </div>

        <!-- Column 2: Products -->
        <div class="col-12 col-md product-grid" id="productGrid">
            <div class="sticky-top pb-3 pt-1 mb-2" style="z-index:10;background:var(--bg-primary);">
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-lg flex-grow-1" style="border-radius:12px;overflow:hidden;box-shadow:var(--card-shadow);">
                        <span class="input-group-text" style="background:var(--bg-input);border:1.5px solid var(--border-input);border-right:none;border-radius:12px 0 0 12px;">
                            <i class="fa-solid fa-search" style="color:var(--text-muted);"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o código..." autofocus
                               style="border-radius:0 12px 12px 0;border:1.5px solid var(--border-input);border-left:none;">
                    </div>
                    <div class="btn-group" role="group" aria-label="Vista" style="flex-shrink:0;">
                        <button id="btnViewFull" type="button" class="btn btn-sm active"
                                title="Vista completa"
                                style="background:var(--bg-card);border:1.5px solid var(--border-input);color:var(--text-primary);border-radius:10px 0 0 10px;padding:10px 12px;">
                            <i class="fa-solid fa-th-large"></i>
                        </button>
                        <button id="btnViewSimple" type="button" class="btn btn-sm"
                                title="Vista simple (sin imágenes)"
                                style="background:var(--bg-card);border:1.5px solid var(--border-input);border-left:none;color:var(--text-primary);border-radius:0 10px 10px 0;padding:10px 12px;">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            @if($recomendados->isNotEmpty())
            <div class="recommended-row">
                <span class="recommended-label">
                    <i class="fa-solid fa-fire" style="color:#f97316;"></i> Top
                </span>
                @foreach($recomendados as $rec)
                <button type="button" class="rec-item"
                    onclick="addToCart('{{$rec->id}}', '{{addslashes($rec->nombre)}}', {{$rec->precio ?? 0}}, {{ (int)$rec->cantidad }}, '{{$rec->sigla ?? 'UND'}}')">
                    <span class="rec-item-name">{{$rec->nombre}}</span>
                    <span class="rec-item-price">{{$empresa->moneda->simbolo ?? '$'}} {{ number_format($rec->precio ?? 0, 0, ',', '.') }}</span>
                </button>
                @endforeach
            </div>
            @endif

            <div class="row g-3" id="productsContainer">
                @foreach ($productos as $item)
                <div class="col-6 col-md-3 col-lg-20 product-item"
                     id="product-{{$item->id}}"
                     data-stock="{{$item->cantidad}}"
                     data-category="{{$item->categoria_id}}"
                     data-nombre="{{ strtoupper(substr(trim($item->nombre), 0, 1)) }}"
                     data-search="{{ strtolower($item->nombre . ' ' . $item->codigo) }}">
                    @php
                        $localImageUrl = $item->image_url;
                    @endphp
                    <div class="card h-100 product-card shadow-sm border-0" onclick="addToCart('{{$item->id}}', '{{addslashes($item->nombre)}}', {{$item->precio ?? 0}}, parseInt(this.closest('.product-item').getAttribute('data-stock')), '{{$item->sigla ?? 'UND'}}')">
                        <div class="product-img-container">
                            @if($item->img_path)
                                <img src="{{ $item->image_url }}"
                                     data-fallback="{{ $localImageUrl }}"
                                     class="product-img"
                                     alt="{{$item->nombre}}"
                                     onerror="if(this.dataset.fallback && this.src !== this.dataset.fallback){ this.src = this.dataset.fallback; this.dataset.fallback=''; } else { this.parentElement.innerHTML='<div class=\'text-muted text-center p-3\'><i class=\'fa-solid fa-image fa-3x mb-2 opacity-25\'></i><br><small>Sin imagen</small></div>'; }">
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="fa-solid fa-image fa-3x mb-2 opacity-25"></i>
                                    <br><small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <div class="product-price">{{$empresa->moneda->simbolo ?? '$'}} {{ number_format($item->precio ?? 0, 0, ',', '.') }}</div>
                            <h6 class="card-title mb-1 product-name" title="{{$item->nombre}}">{{$item->nombre}}</h6>
                            <small class="text-{{ $item->cantidad > 5 ? 'success' : ($item->cantidad > 0 ? 'warning' : 'danger') }} d-block stock-display" style="font-size: 0.7rem;">
                                Stock: <span class="stock-count">{{$item->cantidad}}</span>
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Barra alfabética — columna independiente, fuera del scroll de productos -->
        <div id="alpha-bar">
            @foreach(range('A','Z') as $letter)
            <span class="alpha-letter" data-letter="{{ $letter }}">{{ $letter }}</span>
            @endforeach
        </div>

        <!-- Column 3: Cart -->
        <div class="col-md-3 cart-section shadow-lg">
            <div class="d-flex align-items-center"
                 style="background:var(--bg-sidebar);border-bottom:1px solid var(--border-sidebar);padding:6px 8px;gap:6px;">
                <div class="d-flex gap-1 flex-grow-1" id="cartTabsContainer"></div>
                <span class="badge" id="cartCount"
                      style="background:var(--color-accent);color:var(--color-secondary);font-family:'JetBrains Mono',monospace;font-weight:700;font-size:0.9rem;white-space:nowrap;">0</span>
                <button type="button" class="cart-add-btn" id="btnAddCart" onclick="addNewCart()" title="Nuevo carrito">
                    <i class="fa-solid fa-plus"></i>
                </button>
                @can('crear-venta')
                <button type="button" class="cart-orders-btn ms-1" id="btnPendingOrders"
                        onclick="toggleOrdersPanel()" title="Pedidos pendientes"
                        style="display:none;" data-can-ver-pedidos="1">
                    <i class="fa-solid fa-bell"></i>
                    <span id="pendingOrdersCount" class="cart-orders-badge">0</span>
                </button>
                @endcan
            </div>
            
            <div class="d-none">
                <select name="comprobante_id"><option value="{{$comprobantes->first()->id ?? ''}}" selected></option></select>
                <select name="metodo_pago" id="selectMetodoPago">
                    @foreach($optionsMetodoPago as $op)
                        <option value="{{ $op->value }}" {{ $loop->first ? 'selected' : '' }}>{{ $op->value }}</option>
                    @endforeach
                    <option value="BOLD">BOLD</option>
                    <option value="MIXTO">MIXTO</option>
                </select>
            </div>

            <div id="cartNameBar" style="background:var(--bg-sidebar);border-bottom:1px solid rgba(255,255,255,0.1);padding:4px 8px;">
                <input type="text" id="cartNameInput" maxlength="30"
                       placeholder="Nombre del carrito..."
                       style="width:100%;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:6px;padding:5px 10px;color:#fff;font-size:0.82rem;font-weight:600;outline:none;transition:border-color 0.2s,background 0.2s;"
                       oninput="updateCartName(this.value)"
                       onkeydown="if(event.key==='Enter')this.blur()"
                       onfocus="this.style.borderColor='var(--color-accent)';this.style.background='rgba(255,255,255,0.14)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.15)';this.style.background='rgba(255,255,255,0.08)'">
            </div>

            <div class="orders-panel" id="ordersPanel">
                <div class="p-2 d-flex justify-content-between align-items-center"
                     style="background:var(--bg-sidebar);color:#fff;font-size:0.8rem;font-weight:700;">
                    <span><i class="fa-solid fa-clock me-1" style="color:#f97316;"></i> Pedidos pendientes</span>
                    <button type="button" onclick="toggleOrdersPanel()"
                            style="background:none;border:none;color:#fff;font-size:0.9rem;cursor:pointer;">✕</button>
                </div>
                <div id="ordersList">
                    <div class="text-center text-muted p-3" style="font-size:0.85rem;">Sin pedidos pendientes</div>
                </div>
            </div>

            <div class="cart-items" id="cartItemsContainer">
                <div class="text-center text-muted mt-5" id="emptyCartMessage">
                    <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-50"></i>
                    <p>Carrito vacío</p>
                    <button type="button" id="btnDeleteEmptyCart" class="btn btn-sm btn-outline-danger mt-1"
                            onclick="removeCart(activeCartIndex)"
                            style="display:none;font-size:0.8rem;">
                        <i class="fa-solid fa-trash me-1"></i> Eliminar carrito
                    </button>
                </div>
                <div id="cartList"></div>
            </div>

            <div class="cart-footer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fs-6 fw-bold text-secondary">TOTAL:</span>
                    <span class="total-display">{{$empresa->moneda->simbolo ?? '$'}} <span id="totalDisplay">0</span></span>
                </div>
                
                <input type="hidden" name="subtotal" id="inputSubtotal" value="0">

                <input type="hidden" name="total" id="inputTotal" value="0">

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small text-muted mb-0">Método de Pago:</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btnToggleSplit" onclick="toggleSplitPay()" title="Dividir pago entre varios métodos">
                                <i class="fa-solid fa-scissors me-1"></i>Dividir
                            </button>
                            <span id="paymentBadge" class="badge bg-success" style="font-size:0.8rem;">
                                <i class="fa-solid fa-money-bill me-1"></i> EFECTIVO
                            </span>
                        </div>
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-3">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:#5C2D91;color:#fff;border-color:#5C2D91;font-size:0.68rem;"
                                onclick="pagarCon('NEQUI')">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:2px;margin-bottom:1px"><rect width="24" height="24" rx="6" fill="white" fill-opacity="0.2"/><path d="M6 17V7l4.5 7V7M13.5 7v10l4.5-7v7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>NEQUI
                            </button>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:#CC0000;color:#fff;border-color:#CC0000;font-size:0.68rem;"
                                onclick="pagarCon('DAVIPLATA')">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:2px;margin-bottom:1px"><rect width="24" height="24" rx="6" fill="white" fill-opacity="0.2"/><path d="M12 3L21 8.5V15.5L12 21L3 15.5V8.5L12 3Z" fill="white" fill-opacity="0.9"/><path d="M9 12L11.5 14.5L16 9.5" stroke="#CC0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>DAVI
                            </button>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:#FF6B00;color:#fff;border-color:#FF6B00;font-size:0.68rem;"
                                onclick="pagarCon('BOLD')">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:2px;margin-bottom:1px"><rect width="24" height="24" rx="6" fill="white" fill-opacity="0.2"/><text x="4" y="17" font-size="13" font-weight="900" fill="white" font-family="Arial">B</text></svg>BOLD
                            </button>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-sm w-100 smart-cash-btn fw-bold"
                                style="background:#1a7340;color:#fff;border-color:#1a7340;font-size:0.68rem;"
                                onclick="pagarEfectivo()">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:2px;margin-bottom:1px"><rect width="24" height="24" rx="6" fill="white" fill-opacity="0.2"/><rect x="3" y="7" width="18" height="10" rx="2" fill="white" fill-opacity="0.9"/><circle cx="12" cy="12" r="2.5" fill="#1a7340"/><path d="M6 12h.5M17.5 12H18" stroke="#1a7340" stroke-width="1.5" stroke-linecap="round"/></svg>EFECT.
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-2" id="smartCashWrapper">
                    <label class="form-label small text-muted mb-1">Pago Rápido (Efectivo):</label>
                    <div style="overflow-y:auto;max-height:110px;scrollbar-width:thin;">
                        <div class="row g-1 flex-nowrap mb-1">
                            <div class="col-auto" style="min-width:56px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();setExactCash()">Exacto</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(1000)">$1k</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(2000)">$2k</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(5000)">$5k</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(10000)">$10k</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(20000)">$20k</button></div>
                            <div class="col-auto" style="min-width:50px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(50000)">$50k</button></div>
                            <div class="col-auto" style="min-width:56px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(100000)">$100k</button></div>
                            <div class="col-auto" style="min-width:56px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(200000)">$200k</button></div>
                            <div class="col-auto" style="min-width:56px;"><button type="button" class="btn btn-outline-secondary w-100 smart-cash-btn" onclick="pagarEfectivo();addCash(500000)">$500k</button></div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3" id="efectivoCampos">
                    <div class="col-6">
                        <label class="small text-muted">Recibido</label>
                        <input type="text" id="dinero_recibido_display" class="form-control fw-bold" placeholder="0" oninput="updateReceived(this)">
                        <input type="hidden" id="dinero_recibido" name="monto_recibido">
                    </div>
                    <div class="col-6">
                        <label class="small text-muted">Vuelto</label>
                        <input type="text" id="vuelto_display" class="form-control fw-bold text-success bg-white" readonly placeholder="0">
                        <input type="hidden" id="vuelto" name="vuelto_entregado">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg fw-bold py-3 w-50" id="btnPay" disabled data-no-spinner="true">
                        <i class="fa-solid fa-cash-register me-2"></i> COBRAR
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg fw-bold py-3 w-50" onclick="cancelarVenta()">
                        <i class="fa-solid fa-times me-1"></i> Cancelar
                    </button>
                </div>
                
                <!-- Indicador de atajos de teclado -->
                <div class="keyboard-hint" id="keyboardHint"></div>
            </div>
        </div>
    </div>
    <!-- Mobile Cart Toggle Button -->
    <button type="button" class="cart-toggle-mobile" id="cartToggleMobile" onclick="toggleMobileCart()">
        <i class="fa-solid fa-shopping-cart"></i>
        <span class="badge" id="cartCountMobile">0</span>
    </button>

    <input type="hidden" name="pagos_mixtos_json" id="inputPagosMixtos">

    <!-- ── Modal Pago Dividido ── -->
    <div class="modal fade" id="modalSplitPay" tabindex="-1" aria-labelledby="modalSplitPayLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-scissors" style="color:var(--color-accent);font-size:1.1rem;"></i>
                            <h5 class="modal-title mb-0 fw-bold" id="modalSplitPayLabel" style="color:#fff;">Dividir Pago</h5>
                        </div>
                        <div class="split-total-display" id="splitModalTotal">$0</div>
                        <div style="font-size:0.78rem;color:rgba(255,255,255,0.6);margin-top:2px;">Total a cobrar</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto align-self-start" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Nequi -->
                    <div class="split-method-card">
                        <div class="split-method-card-header">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="7" fill="#5C2D91"/><path d="M6 17V7l4.5 7V7M13.5 7v10l4.5-7v7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="split-method-card-title" style="color:#5C2D91;">Nequi</span>
                        </div>
                        <input type="text" id="splitNequi" class="split-method-card-input" placeholder="$ 0" oninput="onSplitInput()" inputmode="numeric">
                    </div>
                    <!-- Daviplata -->
                    <div class="split-method-card">
                        <div class="split-method-card-header">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="7" fill="#CC0000"/><path d="M12 3L21 8.5V15.5L12 21L3 15.5V8.5L12 3Z" fill="white" fill-opacity="0.9"/></svg>
                            <span class="split-method-card-title" style="color:#CC0000;">Daviplata</span>
                        </div>
                        <input type="text" id="splitDaviplata" class="split-method-card-input" placeholder="$ 0" oninput="onSplitInput()" inputmode="numeric">
                    </div>
                    <!-- Bold -->
                    <div class="split-method-card">
                        <div class="split-method-card-header">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="7" fill="#FF6B00"/><text x="5" y="18" font-size="14" font-weight="900" fill="white" font-family="Arial">B</text></svg>
                            <span class="split-method-card-title" style="color:#FF6B00;">Bold</span>
                        </div>
                        <input type="text" id="splitBold" class="split-method-card-input" placeholder="$ 0" oninput="onSplitInput()" inputmode="numeric">
                    </div>
                    <!-- Efectivo -->
                    <div class="split-method-card">
                        <div class="split-method-card-header">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="7" fill="#1a7340"/><rect x="3" y="7" width="18" height="10" rx="2" fill="white" fill-opacity="0.9"/><circle cx="12" cy="12" r="2" fill="#1a7340"/></svg>
                            <span class="split-method-card-title" style="color:#1a7340;">Efectivo</span>
                        </div>
                        <input type="text" id="splitEfectivo" class="split-method-card-input" placeholder="$ 0" oninput="onSplitInput()" inputmode="numeric">
                    </div>

                    <!-- Barra de progreso -->
                    <div class="split-progress-bar">
                        <div class="split-progress-fill" id="splitProgressFill"></div>
                    </div>
                    <div class="split-summary-row" id="splitSummaryAssigned">
                        <span>Asignado</span>
                        <span class="val" id="splitAssigned">$0</span>
                    </div>
                    <div class="split-summary-row pending" id="splitSummaryRemaining">
                        <span>Pendiente</span>
                        <span class="val" id="splitRemaining">$0</span>
                    </div>
                    <div class="split-summary-row ok" id="splitSummaryVuelto" style="display:none;">
                        <span>Vuelto (efectivo)</span>
                        <span class="val" id="splitVueltoDisplay">$0</span>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" onclick="cancelSplitPay()">
                        <i class="fa-solid fa-times me-1"></i> Cancelar
                    </button>
                    <button type="button" id="btnConfirmSplit" onclick="confirmSplitPay()" disabled>
                        <i class="fa-solid fa-check me-2"></i> Confirmar pago
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Overlay para cerrar el carrito móvil tocando fuera -->
    <div class="mobile-cart-overlay" id="mobileCartOverlay" onclick="toggleMobileCart()"></div>
</form>
@endsection

@push('js')
<script>
    // ── UUID helper ──
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    // ── Multi-cart state ──
    var carts = [{ uuid: generateUUID(), id: 1, items: [], metodoPago: 'EFECTIVO', dineroRecibido: 0, vuelto: 0, name: '' }];
    var activeCartIndex = 0;
    var _syncTimeout = null;
    // Transparent proxy: existing code using `cart` automatically targets the active cart
    Object.defineProperty(window, 'cart', {
        get: function() { return carts[activeCartIndex].items; },
        set: function(v) { carts[activeCartIndex].items = v; },
        configurable: true
    });
    var total = 0;
    var soundEnabled = true; // Cambiar a false para desactivar sonidos

    // Sonidos simples usando Web Audio API
    function playSound(frequency, duration) {
        if (!soundEnabled) return;
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + duration);
        } catch(e) {
            // Silenciar errores de audio
        }
    }

    function playSuccessSound() {
        if (!soundEnabled) return;
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const notes = [523, 659, 784, 1047]; // Do, Mi, Sol, Do (acorde C mayor)
            notes.forEach(function(freq, i) {
                const osc = audioContext.createOscillator();
                const gain = audioContext.createGain();
                osc.connect(gain);
                gain.connect(audioContext.destination);
                osc.type = 'sine';
                osc.frequency.value = freq;
                const start = audioContext.currentTime + i * 0.1;
                gain.gain.setValueAtTime(0.18, start);
                gain.gain.exponentialRampToValueAtTime(0.001, start + 0.25);
                osc.start(start);
                osc.stop(start + 0.25);
            });
        } catch(e) {}
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function normalizeMoney(value) {
        var parsed = parseFloat(value);
        if (isNaN(parsed) || parsed < 0) {
            return 0;
        }

        return Math.round(parsed * 100) / 100;
    }

    function parseMoneyInput(value) {
        var digits = String(value || '').replace(/\D/g, '');
        if (!digits) {
            return 0;
        }

        return parseInt(digits, 10);
    }

    function recalculateItem(item) {
        item.precio = normalizeMoney(item.precio);
        item.subtotal = normalizeMoney(item.cantidad * item.precio);
    }

    function updateCartTotals() {
        total = 0;
        cart.forEach(function(item) {
            total += item.subtotal;
        });

        document.getElementById('totalDisplay').innerText = formatNumber(total);
        document.getElementById('inputTotal').value = total;
        document.getElementById('inputSubtotal').value = total;

        var totalItems = cart.reduce(function(acc, item) { return acc + item.cantidad; }, 0);
        document.getElementById('cartCount').innerText = totalItems;
        var mobileCount = document.getElementById('cartCountMobile');
        if (mobileCount) mobileCount.innerText = totalItems;

        calculateChange();
        persistCarts();
    }

    function showKeyboardHint(text) {
        const hint = document.getElementById('keyboardHint');
        hint.textContent = text;
        hint.classList.add('show');
        setTimeout(() => {
            hint.classList.remove('show');
        }, 1500);
    }

    document.addEventListener('DOMContentLoaded', async function() {
        var loadedFromServer = await loadCartsFromServer();
        if (!loadedFromServer) {
            loadPersistedCarts();
        }
        renderCart();
        loadCartPaymentState();
        renderCartTabs();
        loadCartName();

        // Auto-collapse sidebar with animation
        if (!document.body.classList.contains('sb-sidenav-toggled')) {
            setTimeout(function() {
                document.body.classList.add('sb-sidenav-toggled');
            }, 500); // Delay to show animation
        }

        // Auto-focus en búsqueda
        const searchInput = document.getElementById('searchInput');
        searchInput.focus();

        // Búsqueda mejorada
        searchInput.addEventListener('keyup', function(e) {
            // Si presiona Enter en la búsqueda y hay un solo resultado, agregarlo
            if (e.key === 'Enter') {
                const visibleProducts = document.querySelectorAll('.product-item[style=""], .product-item:not([style*="display: none"])');
                if (visibleProducts.length === 1) {
                    visibleProducts[0].querySelector('.product-card').click();
                    this.value = '';
                    this.dispatchEvent(new Event('keyup')); // Limpiar filtro
                }
                return;
            }

            var value = this.value.toLowerCase();
            var items = document.querySelectorAll('.product-item');
            items.forEach(function(item) {
                var search = item.getAttribute('data-search');
                if (search.indexOf(value) > -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Mantener foco en búsqueda después de agregar productos
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.cart-section') && !e.target.closest('input')) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        // Atajos de teclado globales
        document.addEventListener('keydown', function(e) {


            // F9: Pago exacto
            if (e.key === 'F9') {
                e.preventDefault();
                setExactCash();
                showKeyboardHint('Pago Exacto');
                return;
            }

            // F10: $10k
            if (e.key === 'F10') {
                e.preventDefault();
                addCash(10000);
                showKeyboardHint('$10,000');
                return;
            }

            // F11: $20k
            if (e.key === 'F11') {
                e.preventDefault();
                addCash(20000);
                showKeyboardHint('$20,000');
                return;
            }

            // F12: $50k
            if (e.key === 'F12') {
                e.preventDefault();
                addCash(50000);
                showKeyboardHint('$50,000');
                return;
            }

            // Enter: Cobrar (si está habilitado)
            if (e.key === 'Enter' && !e.target.matches('#searchInput')) {
                const btnPay = document.getElementById('btnPay');
                if (!btnPay.disabled) {
                    e.preventDefault();
                    btnPay.click();
                }
                return;
            }

            // Escape: Cancelar venta
            if (e.key === 'Escape') {
                e.preventDefault();
                if (cart.length > 0) {
                    Swal.fire({
                        title: '¿Cancelar venta?',
                        text: 'Se perderán todos los productos del carrito',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, cancelar',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            cancelarVenta();
                            showKeyboardHint('Venta cancelada');
                        }
                    });
                }
                return;
            }

            // / (slash): Enfocar búsqueda
            if (e.key === '/' && !e.target.matches('input')) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
                return;
            }
        });
    });

    // ── Category sidebar toggle ──
    function toggleCategorySidebar() {
        var sidebar = document.getElementById('categorySidebar');
        var grid    = document.getElementById('productsContainer');
        var isCompact = sidebar.classList.toggle('compact');
        if (grid) grid.classList.toggle('cat-compact', isCompact);
        var chevron = sidebar.querySelector('.cat-toggle-icon');
        if (chevron) {
            chevron.className = isCompact
                ? 'fa-solid fa-chevron-right cat-toggle-icon ms-auto'
                : 'fa-solid fa-chevron-left cat-toggle-icon ms-auto';
        }
        localStorage.setItem('pos_cat_compact', isCompact ? '1' : '0');
    }

    // Restaurar estado al cargar
    (function() {
        if (localStorage.getItem('pos_cat_compact') === '1') {
            var sidebar = document.getElementById('categorySidebar');
            var grid    = document.getElementById('productsContainer');
            if (sidebar) {
                sidebar.classList.add('compact');
                var chevron = sidebar.querySelector('.cat-toggle-icon');
                if (chevron) chevron.className = 'fa-solid fa-chevron-right cat-toggle-icon ms-auto';
            }
            if (grid) grid.classList.add('cat-compact');
        }
    })();

    function filterCategory(catId, btn) {
        var buttons = document.querySelectorAll('.category-btn');
        buttons.forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        
        var items = document.querySelectorAll('.product-item');
        if(catId === 'all') {
            items.forEach(function(item) { item.style.display = ''; });
        } else {
            items.forEach(function(item) {
                if(item.getAttribute('data-category') == catId) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Re-enfocar búsqueda
        setTimeout(() => document.getElementById('searchInput').focus(), 100);
    }

    function addToCart(id, nombre, precio, stock, sigla) {
        id = id.toString();
        var existingItem = cart.find(function(item) { return item.id === id; });
        var precioBase = normalizeMoney(precio);

        // Sonido de éxito
        playSound(800, 0.1);

        if (existingItem) {
            existingItem.cantidad++;
            recalculateItem(existingItem);
        } else {
            cart.push({ 
                id: id, 
                nombre: nombre, 
                precio: precioBase, 
                cantidad: 1, 
                sigla: sigla, 
                stock: parseInt(stock),
                subtotal: precioBase,
                isNew: true
            });
        }
        renderCart();
        
        // Mostrar notificación toast
        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            text: nombre,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    }

    function updateQuantityManual(id, newQty, maxStock) {
        newQty = parseInt(newQty);
        
        if (isNaN(newQty) || newQty < 1) {
            playSound(200, 0.2);
            Swal.fire({ 
                icon: 'warning', 
                title: 'Cantidad mínima: 1', 
                toast: true, 
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 2000 
            });
            renderCart();
            return;
        }
        
        var item = cart.find(function(i) { return i.id === id; });
        if (item) {
            item.cantidad = newQty;
            recalculateItem(item);
            playSound(600, 0.1);
            renderCart();
        }
    }

    function cartQtyDecrement(id, maxStock) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;
        if (item.cantidad <= 1) {
            cart = cart.filter(function(i) { return i.id !== id; });
            playSound(400, 0.15);
            renderCart();
            renderCartTabs();
        } else {
            item.cantidad--;
            recalculateItem(item);
            playSound(600, 0.08);
            renderCart();
        }
    }

    function cartQtyIncrement(id, maxStock) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;
        if (maxStock > 0 && item.cantidad >= maxStock) {
            playSound(200, 0.2);
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: 'No hay más unidades disponibles',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1800
            });
            return;
        }
        item.cantidad++;
        recalculateItem(item);
        playSound(600, 0.08);
        renderCart();
    }

    function updatePriceManual(id, newPrice) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;

        var precio = parseMoneyInput(newPrice);

        if (precio <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Precio inválido',
                text: 'El precio por producto debe ser mayor a cero.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1800
            });
            renderCart();
            return;
        }

        item.precio = precio;
        recalculateItem(item);
        playSound(550, 0.08);
        renderCart();
    }

    function syncPriceInput(input, id) {
        var item = cart.find(function(i) { return i.id === id; });
        if (!item) return;

        var precio = parseMoneyInput(input.value);
        item.precio = precio;
        recalculateItem(item);

        input.value = precio > 0 ? formatNumber(precio) : '';

        var hiddenPrice = document.getElementById('cart-hidden-price-' + id);
        if (hiddenPrice) {
            hiddenPrice.value = item.precio;
        }

        var subtotalNode = document.getElementById('cart-subtotal-' + id);
        if (subtotalNode) {
            subtotalNode.innerText = formatNumber(item.subtotal);
        }

        updateCartTotals();
    }

    function removeFromCart(id) {
        playSound(400, 0.15);
        Swal.fire({
            title: '¿Eliminar producto?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = cart.filter(function(i) { return i.id !== id; });
                renderCart();
                Swal.fire({
                    icon: 'success',
                    title: 'Producto eliminado',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    }

    function renderCart() {
        var container = document.getElementById('cartList');
        container.innerHTML = '';
        total = 0;

        if (cart.length === 0) {
            document.getElementById('emptyCartMessage').style.display = 'block';
            document.getElementById('btnPay').disabled = true;
            var btnDel = document.getElementById('btnDeleteEmptyCart');
            if (btnDel) btnDel.style.display = carts.length > 1 ? 'inline-block' : 'none';
        } else {
            document.getElementById('emptyCartMessage').style.display = 'none';
            
            cart.forEach(function(item) {
                var itemClass = item.isNew ? 'cart-item cart-item-new' : 'cart-item';
                item.isNew = false; // Resetear flag
                
                var row = '<div class="' + itemClass + '" id="cart-row-' + item.id + '">' +
                    '<div class="flex-grow-1 cart-item-details">' +
                        '<div class="fw-bold text-truncate" style="max-width: 170px;">' + item.nombre + '</div>' +
                        '<div class="cart-item-controls">' +
                            '<div class="cart-item-field">' +
                                '<label>Cantidad</label>' +
                                '<div class="d-flex align-items-center gap-1">' +
                                    '<button type="button" class="cart-qty-btn minus" tabindex="-1" onclick="cartQtyDecrement(\'' + item.id + '\', ' + item.stock + ')">−</button>' +
                                    '<input type="number" class="form-control form-control-sm cart-qty-input" ' +
                                        'value="' + item.cantidad + '" ' +
                                        'min="1" ' +
                                        'onchange="updateQuantityManual(\'' + item.id + '\', this.value, ' + item.stock + ')" ' +
                                        'onclick="this.select()">' +
                                    '<button type="button" class="cart-qty-btn plus" tabindex="-1" onclick="cartQtyIncrement(\'' + item.id + '\', ' + item.stock + ')">+</button>' +
                                    '<small class="text-muted ms-1">' + item.sigla + '</small>' +
                                '</div>' +
                            '</div>' +
                            '<div class="cart-item-field">' +
                                '<label>Precio</label>' +
                                '<input type="text" class="form-control form-control-sm cart-price-input" ' +
                                    'value="' + formatNumber(item.precio) + '" ' +
                                    'inputmode="numeric" autocomplete="off" ' +
                                    'oninput="syncPriceInput(this, \'' + item.id + '\')" ' +
                                    'onblur="updatePriceManual(\'' + item.id + '\', this.value)" ' +
                                    'onclick="this.select()">' +
                            '</div>' +
                        '</div>' +
                        '<input type="hidden" name="arrayidproducto[]" value="' + item.id + '">' +
                        '<input type="hidden" name="arraycantidad[]" value="' + item.cantidad + '">' +
                        '<input type="hidden" name="arrayprecioventa[]" id="cart-hidden-price-' + item.id + '" value="' + item.precio + '">' +
                    '</div>' +
                    '<div class="text-end ms-2">' +
                        '<div class="cart-item-subtotal mb-2" id="cart-subtotal-' + item.id + '">' + formatNumber(item.subtotal) + '</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeFromCart(\'' + item.id + '\')" title="Eliminar">' +
                            '<i class="fa-solid fa-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
                container.insertAdjacentHTML('beforeend', row);
            });
        }

        updateCartTotals();
    }

    function setExactCash() {
        if(total === 0) return;
        document.getElementById('dinero_recibido').value = total;
        document.getElementById('dinero_recibido_display').value = formatNumber(total);
        playSound(600, 0.1);
        calculateChange();
    }

    function addCash(amount) {
        if(total === 0) return;
        document.getElementById('dinero_recibido').value = amount;
        document.getElementById('dinero_recibido_display').value = formatNumber(amount);
        playSound(600, 0.1);
        calculateChange();
    }

    function updateReceived(input) {
        var val = input.value.replace(/\D/g, '');
        var num = parseFloat(val);
        
        if(isNaN(num)) {
            document.getElementById('dinero_recibido').value = 0;
            input.value = '';
        } else {
            document.getElementById('dinero_recibido').value = num;
            input.value = formatNumber(num);
        }
        calculateChange();
    }

    function calculateChange() {
        var metodoPago = document.getElementById('selectMetodoPago').value;
        if (metodoPago !== 'EFECTIVO') {
            if (total > 0) {
                document.getElementById('dinero_recibido').value = total;
                document.getElementById('dinero_recibido_display').value = formatNumber(total);
                document.getElementById('vuelto').value = 0;
                document.getElementById('vuelto_display').value = '0';
                document.getElementById('btnPay').disabled = false;
            } else {
                document.getElementById('dinero_recibido').value = '';
                document.getElementById('dinero_recibido_display').value = '';
                document.getElementById('vuelto').value = '';
                document.getElementById('vuelto_display').value = '';
                document.getElementById('btnPay').disabled = true;
            }
            return;
        }

        var received = parseFloat(document.getElementById('dinero_recibido').value) || 0;
        
        if (received >= total && total > 0) {
            var change = received - total;
            document.getElementById('vuelto').value = change;
            document.getElementById('vuelto_display').value = formatNumber(change);
            document.getElementById('btnPay').disabled = false;
            playSound(1000, 0.1);
        } else {
            document.getElementById('vuelto').value = '';
            document.getElementById('vuelto_display').value = '';
            document.getElementById('btnPay').disabled = true;
        }
        // Persistir montos de pago en BD
        if (typeof persistCarts === 'function') persistCarts();
    }

    function toggleMobileCart() {
        var cartSection = document.querySelector('.cart-section');
        var overlay = document.getElementById('mobileCartOverlay');
        var isOpen = cartSection.classList.contains('mobile-open');
        if (isOpen) {
            cartSection.classList.remove('mobile-open');
            overlay.classList.remove('active');
        } else {
            cartSection.classList.add('mobile-open');
            overlay.classList.add('active');
        }
    }

    function cancelarVenta() {
        cart = [];
        carts[activeCartIndex].metodoPago = 'EFECTIVO';
        carts[activeCartIndex].dineroRecibido = 0;
        carts[activeCartIndex].vuelto = 0;
        document.getElementById('dinero_recibido').value = '';
        document.getElementById('dinero_recibido_display').value = '';
        document.getElementById('vuelto').value = '';
        document.getElementById('vuelto_display').value = '';
        pagarEfectivo();
        renderCart();
        renderCartTabs();
        persistCarts();
        document.getElementById('searchInput').focus();
    }

    function pagarEfectivo() {
        var select = document.getElementById('selectMetodoPago');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value === 'EFECTIVO') { select.selectedIndex = i; break; }
        }
        var badge = document.getElementById('paymentBadge');
        badge.className = 'badge bg-success';
        badge.style.fontSize = '0.8rem';
        badge.innerHTML = '<i class="fa-solid fa-money-bill me-1"></i> EFECTIVO';
        document.getElementById('efectivoCampos').style.display = '';
        document.getElementById('smartCashWrapper').style.display = '';
    }

    function pagarCon(type) {
        if (total === 0) return;
        var select = document.getElementById('selectMetodoPago');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value === type) { select.selectedIndex = i; break; }
        }
        var badge = document.getElementById('paymentBadge');
        badge.style.fontSize = '0.8rem';
        if (type === 'NEQUI') {
            badge.className = 'badge';
            badge.style.backgroundColor = '#230836';
            badge.style.color = '#fff';
            badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> NEQUI';
        } else if (type === 'DAVIPLATA') {
            badge.className = 'badge bg-danger';
            badge.style.backgroundColor = '';
            badge.style.color = '';
            badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> DAVIPLATA';
        } else if (type === 'BOLD') {
            badge.className = 'badge';
            badge.style.backgroundColor = '#FF6B00';
            badge.style.color = '#fff';
            badge.innerHTML = '<i class="fa-solid fa-bolt me-1"></i> BOLD';
        }
        document.getElementById('efectivoCampos').style.display = 'none';
        document.getElementById('smartCashWrapper').style.display = 'none';
        document.getElementById('dinero_recibido').value = total;
        document.getElementById('dinero_recibido_display').value = formatNumber(total);
        document.getElementById('vuelto').value = 0;
        document.getElementById('vuelto_display').value = '0';
        document.getElementById('btnPay').disabled = false;
        playSound(600, 0.1);
    }

    // Prevenir envío tradicional y usar AJAX (Fetch API)
    document.getElementById('ventaForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar la recarga de página

        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'Agrega productos antes de cobrar'
            });
            return false;
        }

        // Deshabilitar botón para evitar doble cobro
        var btnPay = document.getElementById('btnPay');
        var originalBtnHtml = btnPay.innerHTML;
        btnPay.disabled = true;
        btnPay.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> PROCESANDO...';

        var formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                return response.json().then(data => { throw data; });
            }
        })
        .then(data => {
            // Sonido de éxito
            playSuccessSound();

            // Modal de éxito centrado
            Swal.fire({
                icon: 'success',
                title: '¡Venta exitosa!',
                text: data.message || 'La venta fue registrada correctamente.',
                showConfirmButton: false,
                timer: 900,
                timerProgressBar: true,
                width: '320px',
                customClass: { popup: 'swal-venta-exito' }
            });

            // Descontar inventario en la vista (DOM) sin recargar
            cart.forEach(function(item) {
                var productEl = document.getElementById('product-' + item.id);
                if (productEl) {
                    var currentStock = parseInt(productEl.getAttribute('data-stock')) || 0;
                    var newStock = currentStock - item.cantidad;
                    productEl.setAttribute('data-stock', newStock);
                    
                    var stockDisplay = productEl.querySelector('.stock-display');
                    if (stockDisplay) {
                        stockDisplay.innerHTML = 'Stock: <span class="stock-count">' + newStock + '</span>';
                        if (newStock > 5) {
                            stockDisplay.className = 'text-success d-block stock-display';
                        } else if (newStock > 0) {
                            stockDisplay.className = 'text-warning d-block stock-display';
                        } else {
                            stockDisplay.className = 'text-danger d-block stock-display';
                        }
                    }
                }
            });

            // Limpiar todo para la siguiente venta
            cart = [];
            total = 0;
            carts[activeCartIndex].metodoPago = 'EFECTIVO';
            carts[activeCartIndex].dineroRecibido = 0;
            carts[activeCartIndex].vuelto = 0;
            document.getElementById('inputSubtotal').value = '0';
            document.getElementById('inputTotal').value = '0';

            pagarEfectivo();
            document.getElementById('dinero_recibido').value = '';
            document.getElementById('dinero_recibido_display').value = '';
            document.getElementById('vuelto').value = '';
            document.getElementById('vuelto_display').value = '';
            renderCart(); // Esto repinta el carrito vacío
            renderCartTabs();

            // Restaurar botón
            btnPay.disabled = true; // Sigue disabled porque el carrito está vacío ahora
            btnPay.innerHTML = originalBtnHtml;
            var si = document.getElementById('searchInput');
            si.value = '';
            si.dispatchEvent(new Event('keyup'));
            si.focus();
            // Reset split pay
            if (typeof splitPayActive !== 'undefined' && splitPayActive) {
                splitPayActive = false;
                document.getElementById('btnToggleSplit').classList.remove('active');
                document.getElementById('inputPagosMixtos').value = '';
                clearSplitInputs();
                document.getElementById('smartCashWrapper').style.display = '';
                pagarEfectivo();
            }
        })
        .catch(error => {
            console.error('Error procesando venta:', error);
            let errorMsg = error.message || 'Ocurrió un error inesperado al procesar la venta.';
            if (error.errors) {
                // Errores de validación de Laravel
                let messages = [];
                for (let field in error.errors) {
                    messages.push(error.errors[field][0]);
                }
                errorMsg = messages.join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });

            // Restaurar botón
            btnPay.disabled = false;
            btnPay.innerHTML = originalBtnHtml;
        });
    });

    // ================================================================
    // BARRA ALFABÉTICA VERTICAL
    // ================================================================
    (function initAlphaBar() {
        var alphaBar       = document.getElementById('alpha-bar');
        var productsEl     = document.getElementById('productsContainer');
        var scrollContainer = document.getElementById('productGrid'); // el div que scrollea
        if (!alphaBar || !productsEl || !scrollContainer) return;

        var letters = alphaBar.querySelectorAll('.alpha-letter');

        // ── Qué letras tienen productos visibles ──
        function updateAvailability() {
            var available = new Set();
            productsEl.querySelectorAll('.product-item').forEach(function(el) {
                if (el.style.display === 'none') return;
                var l = (el.getAttribute('data-nombre') || '').toUpperCase();
                if (l) available.add(l);
            });
            letters.forEach(function(el) {
                var l = el.getAttribute('data-letter');
                el.classList.remove('has-items', 'no-items');
                el.classList.add(available.has(l) ? 'has-items' : 'no-items');
            });
        }

        // ── Scroll al primer producto con esa letra (o la siguiente disponible) ──
        function scrollToLetter(letter) {
            letter = letter.toUpperCase();
            var ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var allItems = Array.from(productsEl.querySelectorAll('.product-item')).filter(function(el) {
                return el.style.display !== 'none';
            });

            var target = null;
            // Intentar la letra exacta, luego buscar la siguiente con items
            for (var start = ALPHABET.indexOf(letter); start < ALPHABET.length && !target; start++) {
                var l = ALPHABET[start];
                target = allItems.find(function(el) {
                    return (el.getAttribute('data-nombre') || '').toUpperCase() === l;
                }) || null;
                if (start === ALPHABET.indexOf(letter) && !target) continue; // solo salta si era la pedida
                if (target) break;
            }
            if (!target) return;

            // Resaltar letra activa
            letters.forEach(function(el) { el.classList.remove('active'); });
            var matchLetter = (target.getAttribute('data-nombre') || '').toUpperCase();
            var activeEl = alphaBar.querySelector('[data-letter="' + matchLetter + '"]');
            if (activeEl) activeEl.classList.add('active');

            // Calcular desplazamiento dentro del scrollContainer
            // offsetTop del target relativo al scrollContainer
            var offsetTop = 0;
            var node = target;
            while (node && node !== scrollContainer) {
                offsetTop += node.offsetTop;
                node = node.offsetParent;
            }
            // Restar el alto de la barra de búsqueda sticky (≈ 64px)
            scrollContainer.scrollTo({ top: Math.max(0, offsetTop - 64), behavior: 'smooth' });
        }

        // ── Click ──
        letters.forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                if (el.classList.contains('no-items')) return;
                scrollToLetter(el.getAttribute('data-letter'));
            });
        });

        // ── Touch deslizante ──
        alphaBar.addEventListener('touchstart', function(e) { handleTouch(e); }, { passive: false });
        alphaBar.addEventListener('touchmove',  function(e) { e.preventDefault(); handleTouch(e); }, { passive: false });

        function handleTouch(e) {
            var touch = e.touches[0];
            var el = document.elementFromPoint(touch.clientX, touch.clientY);
            if (el && el.classList.contains('alpha-letter') && !el.classList.contains('no-items')) {
                scrollToLetter(el.getAttribute('data-letter'));
            }
        }

        // ── Resaltar letra activa al hacer scroll ──
        scrollContainer.addEventListener('scroll', function() {
            var containerRect = scrollContainer.getBoundingClientRect();
            var midY = containerRect.top + 64 + 10; // just below sticky search
            var allItems = Array.from(productsEl.querySelectorAll('.product-item')).filter(function(el) {
                return el.style.display !== 'none';
            });
            var current = null;
            for (var i = 0; i < allItems.length; i++) {
                var rect = allItems[i].getBoundingClientRect();
                if (rect.top <= midY) {
                    current = allItems[i];
                } else {
                    break;
                }
            }
            if (current) {
                var l = (current.getAttribute('data-nombre') || '').toUpperCase();
                letters.forEach(function(el) { el.classList.remove('active'); });
                var activeEl = alphaBar.querySelector('[data-letter="' + l + '"]');
                if (activeEl) activeEl.classList.add('active');
            }
        }, { passive: true });

        // ── Actualizar tabs al agregar productos ──
        var _origAddToCart = window.addToCart;
        window.addToCart = function() {
            _origAddToCart.apply(this, arguments);
            renderCartTabs();
        };

        // ── Inicializar ──
        updateAvailability();

        // ── Actualizar al filtrar categoría o buscar ──
        var _origFilter = window.filterCategory;
        window.filterCategory = function(catId, btn) {
            if (_origFilter) _origFilter(catId, btn);
            setTimeout(updateAvailability, 60);
        };
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() { setTimeout(updateAvailability, 60); });
        }
    })();

    // ================================================================
    // MULTI-CART SYSTEM
    // ================================================================

    function persistCarts() {
        try {
            localStorage.setItem('pos_carts', JSON.stringify(carts));
            localStorage.setItem('pos_active_cart', activeCartIndex);
        } catch(e) {}

        // Sync inmediato a BD — sin debounce para no perder datos en apagones
        syncCartsToServer();
    }

    function persistCartsDebounced() {
        try {
            localStorage.setItem('pos_carts', JSON.stringify(carts));
            localStorage.setItem('pos_active_cart', activeCartIndex);
        } catch(e) {}

        // Debounce solo para cambios de nombre (evita sync por cada tecla)
        clearTimeout(_syncTimeout);
        _syncTimeout = setTimeout(syncCartsToServer, 800);
    }

    function syncCartsToServer() {
        fetch('{{ route("pos.carritos.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ carts: carts })
        }).then(function(response) {
            if (!response.ok) {
                console.warn('[CarritoPOS] Sync HTTP error:', response.status);
                window._syncFailures = (window._syncFailures || 0) + 1;
            } else {
                window._syncFailures = 0;
            }
        }).catch(function() {
            window._syncFailures = (window._syncFailures || 0) + 1;
        }); // localStorage actúa como fallback automático
    }

    function syncDeleteCartFromServer(uuid) {
        fetch('{{ route("pos.carritos.destroy", ":uuid") }}'.replace(':uuid', uuid), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).catch(function() {});
    }

    function loadPersistedCarts() {
        try {
            var saved = localStorage.getItem('pos_carts');
            var savedIndex = parseInt(localStorage.getItem('pos_active_cart')) || 0;
            if (saved) {
                var parsed = JSON.parse(saved);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    // Asegurar que carritos viejos (sin uuid) reciban uno
                    parsed.forEach(function(c) { if (!c.uuid) c.uuid = generateUUID(); });
                    carts = parsed;
                    activeCartIndex = savedIndex < parsed.length ? savedIndex : 0;
                }
            }
        } catch(e) {}
    }

    async function loadCartsFromServer() {
        try {
            var response = await fetch('{{ route("pos.carritos.index") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!response.ok) return false;
            var data = await response.json();
            if (!Array.isArray(data) || data.length === 0) return false;

            carts = data.map(function(c, idx) {
                return {
                    uuid:           c.id,
                    id:             idx + 1,
                    name:           c.nombre || '',
                    items:          Array.isArray(c.items) ? c.items : [],
                    metodoPago:     c.metodo_pago || 'EFECTIVO',
                    dineroRecibido: parseFloat(c.dinero_recibido) || 0,
                    vuelto:         parseFloat(c.vuelto) ?? 0,
                };
            });
            activeCartIndex = 0;
            return true;
        } catch(e) {
            return false;
        }
    }

    function saveCartPaymentState() {
        var c = carts[activeCartIndex];
        c.metodoPago = document.getElementById('selectMetodoPago').value;
        c.dineroRecibido = parseFloat(document.getElementById('dinero_recibido').value) || 0;
        c.vuelto = parseFloat(document.getElementById('vuelto').value) || 0;
        persistCarts();
    }

    function loadCartPaymentState() {
        var c = carts[activeCartIndex];
        var select = document.getElementById('selectMetodoPago');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value === c.metodoPago) { select.selectedIndex = i; break; }
        }
        if (c.metodoPago === 'EFECTIVO') {
            pagarEfectivo();
        } else {
            var badge = document.getElementById('paymentBadge');
            badge.style.fontSize = '0.8rem';
            if (c.metodoPago === 'NEQUI') {
                badge.className = 'badge';
                badge.style.backgroundColor = '#230836';
                badge.style.color = '#fff';
                badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> NEQUI';
            } else if (c.metodoPago === 'DAVIPLATA') {
                badge.className = 'badge bg-danger';
                badge.style.backgroundColor = '';
                badge.style.color = '';
                badge.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> DAVIPLATA';
            } else if (c.metodoPago === 'BOLD') {
                badge.className = 'badge';
                badge.style.backgroundColor = '#FF6B00';
                badge.style.color = '#fff';
                badge.innerHTML = '<i class="fa-solid fa-bolt me-1"></i> BOLD';
            }
            document.getElementById('efectivoCampos').style.display = 'none';
            document.getElementById('smartCashWrapper').style.display = 'none';
        }
        document.getElementById('dinero_recibido').value = c.dineroRecibido || '';
        document.getElementById('dinero_recibido_display').value = c.dineroRecibido ? formatNumber(c.dineroRecibido) : '';
        document.getElementById('vuelto').value = c.vuelto || '';
        document.getElementById('vuelto_display').value = c.vuelto ? formatNumber(c.vuelto) : '';
    }

    function switchCart(index) {
        if (index === activeCartIndex) return;
        saveCartPaymentState();
        activeCartIndex = index;
        renderCart();
        loadCartPaymentState();
        renderCartTabs();
        loadCartName();
        setTimeout(function() { document.getElementById('searchInput').focus(); }, 50);
    }

    function addNewCart() {
        saveCartPaymentState();
        var newId = Math.max.apply(null, carts.map(function(c) { return c.id || 0; })) + 1;
        carts.push({ uuid: generateUUID(), id: newId, items: [], metodoPago: 'EFECTIVO', dineroRecibido: 0, vuelto: 0, name: '' });
        activeCartIndex = carts.length - 1;
        renderCart();
        loadCartPaymentState();
        renderCartTabs();
        loadCartName();
        persistCarts(); // Guardar nuevo carrito en BD inmediatamente
        setTimeout(function() { document.getElementById('searchInput').focus(); }, 50);
    }

    function renderCartTabs() {
        var container = document.getElementById('cartTabsContainer');
        if (!container) return;
        container.innerHTML = '';
        carts.forEach(function(c, idx) {
            var isActive = idx === activeCartIndex;
            var hasItems = c.items.length > 0;
            var canDelete = carts.length > 1;
            var cartName = (c.name && c.name.trim()) ? c.name.trim() : '';
            var label = cartName ? cartName[0].toUpperCase() : (idx + 1);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cart-tab' + (isActive ? ' active' : '');
            btn.title = (cartName || ('Carrito ' + (idx + 1))) + (hasItems && !isActive ? ' (en pausa)' : '');
            btn.onclick = (function(i) { return function() { switchCart(i); }; })(idx);
            btn.innerHTML = '<span style="font-size:1rem;font-weight:800;">' + label + '</span>' +
                (hasItems && !isActive ? '<span class="cart-tab-dot"></span>' : '') +
                (canDelete ? '<span class="cart-tab-close" title="Eliminar carrito">✕</span>' : '');
            if (canDelete) {
                var closeBtn = btn.querySelector('.cart-tab-close');
                closeBtn.addEventListener('click', (function(i) {
                    return function(e) { e.stopPropagation(); removeCart(i); };
                })(idx));
            }
            container.appendChild(btn);
        });
        persistCarts();
    }

    function updateCartName(value) {
        carts[activeCartIndex].name = value;
        renderCartTabs();
        persistCarts(); // Inmediato: nombre también se persiste sin debounce
    }

    function loadCartName() {
        var input = document.getElementById('cartNameInput');
        if (!input) return;
        input.value = carts[activeCartIndex].name || '';
    }

    function removeCart(index) {
        var c = carts[index];
        if (c.items.length > 0) {
            Swal.fire({
                title: '¿Eliminar carrito ' + (index + 1) + '?',
                text: 'Tiene ' + c.items.length + ' producto(s) en pausa. Se perderán.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) { doRemoveCart(index); }
            });
        } else {
            doRemoveCart(index);
        }
    }

    function doRemoveCart(index) {
        if (carts.length <= 1) return;
        var removedUuid = carts[index].uuid;
        if (removedUuid) syncDeleteCartFromServer(removedUuid);
        carts.splice(index, 1);
        if (activeCartIndex >= carts.length) {
            activeCartIndex = carts.length - 1;
        } else if (activeCartIndex > index) {
            activeCartIndex--;
        }
        renderCart();
        loadCartPaymentState();
        renderCartTabs();
    }

    // ================================================================
    // PEDIDOS PENDIENTES — POLLING
    // ================================================================
    var _ordersOpen    = false;
    var _lastOrderIds  = [];

    function toggleOrdersPanel() {
        _ordersOpen = !_ordersOpen;
        document.getElementById('ordersPanel').classList.toggle('open', _ordersOpen);
    }

    function loadOrder(orderId) {
        fetch('{{ route("pedidos.tomar", ":id") }}'.replace(':id', orderId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            saveCartPaymentState();
            var newId = Math.max.apply(null, carts.map(function(c) { return c.id; })) + 1;
            carts.push({
                id: newId,
                items: data.items.map(function(item) {
                    return {
                        id: String(item.id),
                        nombre: item.nombre,
                        precio: item.precio,
                        cantidad: item.cantidad,
                        sigla: item.sigla || 'UND',
                        stock: 9999,
                        subtotal: item.subtotal,
                        isNew: false
                    };
                }),
                metodoPago: 'EFECTIVO',
                dineroRecibido: 0,
                vuelto: 0
            });
            activeCartIndex = carts.length - 1;
            renderCart();
            loadCartPaymentState();
            renderCartTabs();
            _ordersOpen = false;
            document.getElementById('ordersPanel').classList.remove('open');
            Swal.fire({
                icon: 'success',
                title: 'Pedido de ' + data.nombre_tomador + ' cargado',
                toast: true, position: 'top-end',
                showConfirmButton: false, timer: 2500, timerProgressBar: true
            });
            pollPendingOrders();
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error al cargar el pedido', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        });
    }

    function pollPendingOrders() {
        fetch('{{ route("pedidos.pendientes") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
        .then(function(orders) {
            var btn     = document.getElementById('btnPendingOrders');
            var countEl = document.getElementById('pendingOrdersCount');
            var listEl  = document.getElementById('ordersList');
            if (!btn) return;

            if (orders.length > 0) {
                btn.style.display = 'inline-flex';
                btn.classList.add('has-orders');
                countEl.textContent = orders.length;

                var newIds = orders.map(function(o) { return o.id; });
                var hasNew = newIds.some(function(id) { return _lastOrderIds.indexOf(id) === -1; });
                if (hasNew && _lastOrderIds.length > 0) {
                    playSound(880, 0.15);
                }
                _lastOrderIds = newIds;

                listEl.innerHTML = orders.map(function(o) {
                    var itemNames = o.items.slice(0,2).map(function(i){ return i.nombre; }).join(', ');
                    if (o.items.length > 2) itemNames += '...';
                    return '<div class="order-item">' +
                        '<div style="flex:1;min-width:0;">' +
                            '<div style="font-weight:700;font-size:0.82rem;">' + o.nombre_tomador + '</div>' +
                            '<div style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + itemNames + '</div>' +
                            '<div style="font-size:0.7rem;color:var(--text-muted);">' + o.created_at_human + '</div>' +
                        '</div>' +
                        '<div style="text-align:right;flex-shrink:0;">' +
                            '<div style="font-weight:800;font-size:0.82rem;font-family:\'JetBrains Mono\',monospace;color:var(--color-success);">$' + o.total.toLocaleString() + '</div>' +
                            '<button type="button" class="btn btn-sm btn-success mt-1" style="font-size:0.72rem;padding:3px 8px;" onclick="loadOrder(' + o.id + ')">' +
                                '<i class="fa-solid fa-cart-plus me-1"></i>Cargar' +
                            '</button>' +
                        '</div>' +
                    '</div>';
                }).join('');
            } else {
                btn.classList.remove('has-orders');
                countEl.textContent = '0';
                _lastOrderIds = [];
                listEl.innerHTML = '<div class="text-center text-muted p-3" style="font-size:0.85rem;">Sin pedidos pendientes</div>';
            }
        })
        .catch(function() {});
    }

    // Solo iniciar el polling si el usuario tiene el permiso (botón presente en DOM)
    if (document.querySelector('[data-can-ver-pedidos]')) {
        pollPendingOrders();
        setInterval(pollPendingOrders, 6000);
    }

    // ── Sync periódico cada 15s — red de seguridad ante apagones ──
    setInterval(syncCartsToServer, 15000);

    // ── Sync a BD antes de cerrar el tab o el navegador ──
    // keepalive permite que el fetch complete incluso si la página se está cerrando
    window.addEventListener('beforeunload', function() {
        clearTimeout(_syncTimeout);
        try {
            fetch('{{ route("pos.carritos.sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ carts: carts }),
                keepalive: true
            });
        } catch(e) {}
    });

    // ── Toggle vista simple / completa ────────────────────────────────────
    (function() {
        const STORAGE_KEY = 'pos_view_mode';
        const container   = document.getElementById('productsContainer');
        const btnFull     = document.getElementById('btnViewFull');
        const btnSimple   = document.getElementById('btnViewSimple');

        function applyMode(mode) {
            if (mode === 'simple') {
                container.classList.add('pos-view-simple');
                btnFull.classList.remove('active');
                btnSimple.classList.add('active');
                btnSimple.style.background = 'var(--color-primary)';
                btnSimple.style.color      = '#fff';
                btnFull.style.background   = 'var(--bg-card)';
                btnFull.style.color        = 'var(--text-primary)';
            } else {
                container.classList.remove('pos-view-simple');
                btnSimple.classList.remove('active');
                btnFull.classList.add('active');
                btnFull.style.background   = 'var(--color-primary)';
                btnFull.style.color        = '#fff';
                btnSimple.style.background = 'var(--bg-card)';
                btnSimple.style.color      = 'var(--text-primary)';
            }
        }

        btnFull.addEventListener('click', function() {
            localStorage.setItem(STORAGE_KEY, 'full');
            applyMode('full');
        });

        btnSimple.addEventListener('click', function() {
            localStorage.setItem(STORAGE_KEY, 'simple');
            applyMode('simple');
        });

        // Aplicar preferencia guardada al cargar
        applyMode(localStorage.getItem(STORAGE_KEY) || 'full');
    })();

    // ================================================================
    // PAGO DIVIDIDO (SPLIT PAYMENT) — Modal
    // ================================================================
    var splitPayActive = false;
    var _splitModal = null;

    function getSplitModal() {
        if (!_splitModal) _splitModal = new bootstrap.Modal(document.getElementById('modalSplitPay'));
        return _splitModal;
    }

    function toggleSplitPay() {
        var total = parseFloat(document.getElementById('inputTotal').value) || 0;
        // Mostrar total en el header del modal
        document.getElementById('splitModalTotal').textContent = '$' + total.toLocaleString('es-CO');
        clearSplitInputs();
        onSplitInput();
        getSplitModal().show();
        // Focus al primer campo cuando abra
        document.getElementById('modalSplitPay').addEventListener('shown.bs.modal', function handler() {
            document.getElementById('splitNequi').focus();
            this.removeEventListener('shown.bs.modal', handler);
        });
    }

    function cancelSplitPay() {
        // Si se cancela sin haber confirmado, no cambia nada
        splitPayActive = false;
        document.getElementById('btnToggleSplit').classList.remove('active');
    }

    function confirmSplitPay() {
        var total    = parseFloat(document.getElementById('inputTotal').value) || 0;
        var nequi    = parseSplitAmount('splitNequi');
        var davi     = parseSplitAmount('splitDaviplata');
        var bold     = parseSplitAmount('splitBold');
        var efectivo = parseSplitAmount('splitEfectivo');
        var asignado = nequi + davi + bold + efectivo;

        var pagos = [];
        if (nequi > 0)    pagos.push({ metodo: 'NEQUI',    monto: nequi });
        if (davi > 0)     pagos.push({ metodo: 'DAVIPLATA', monto: davi });
        if (bold > 0)     pagos.push({ metodo: 'BOLD',      monto: bold });
        if (efectivo > 0) pagos.push({ metodo: 'EFECTIVO',  monto: efectivo });

        if (asignado < total || pagos.length < 2) return;

        // Aplicar al formulario
        splitPayActive = true;
        document.getElementById('btnToggleSplit').classList.add('active');
        document.getElementById('selectMetodoPago').value = 'MIXTO';
        document.getElementById('inputPagosMixtos').value = JSON.stringify(pagos);

        var efectivoNecesario = Math.max(0, total - nequi - davi - bold);
        var vuelto = Math.max(0, efectivo - efectivoNecesario);
        document.getElementById('dinero_recibido').value = asignado;
        document.getElementById('vuelto').value = vuelto;

        // Mostrar vuelto si hay efectivo
        if (efectivo > 0 && vuelto > 0) {
            document.getElementById('dinero_recibido_display').value = '$' + asignado.toLocaleString('es-CO');
            document.getElementById('vuelto_display').value = '$' + vuelto.toLocaleString('es-CO');
        }

        // Badge
        var partes = pagos.map(function(p) { return p.metodo; }).join(' + ');
        document.getElementById('paymentBadge').innerHTML = '<i class="fa-solid fa-scissors me-1"></i> ' + partes;
        document.getElementById('paymentBadge').className = 'badge';
        document.getElementById('paymentBadge').style.background = 'linear-gradient(90deg,#5C2D91,#CC0000)';
        document.getElementById('efectivoCampos').style.display = efectivo > 0 ? '' : 'none';
        document.getElementById('smartCashWrapper').style.display = 'none';

        document.getElementById('btnPay').disabled = false;
        getSplitModal().hide();
    }

    function clearSplitInputs() {
        ['splitNequi','splitDaviplata','splitBold','splitEfectivo'].forEach(function(id) {
            document.getElementById(id).value = '';
        });
    }

    function parseSplitAmount(id) {
        var val = (document.getElementById(id).value || '').replace(/[^0-9]/g, '');
        return val ? parseInt(val, 10) : 0;
    }

    function onSplitInput() {
        var total    = parseFloat(document.getElementById('inputTotal').value) || 0;
        var nequi    = parseSplitAmount('splitNequi');
        var davi     = parseSplitAmount('splitDaviplata');
        var bold     = parseSplitAmount('splitBold');
        var efectivo = parseSplitAmount('splitEfectivo');
        var asignado = nequi + davi + bold + efectivo;
        var pendiente = Math.max(0, total - asignado);

        // Actualizar total en header si cambió
        document.getElementById('splitModalTotal').textContent = '$' + total.toLocaleString('es-CO');
        document.getElementById('splitAssigned').textContent = '$' + asignado.toLocaleString('es-CO');

        var remEl = document.getElementById('splitRemaining');
        var summaryRem = document.getElementById('splitSummaryRemaining');
        if (pendiente <= 0) {
            remEl.textContent = '$0';
            summaryRem.className = 'split-summary-row ok';
        } else {
            remEl.textContent = '$' + pendiente.toLocaleString('es-CO');
            summaryRem.className = 'split-summary-row pending';
        }

        // Barra de progreso
        var pct = total > 0 ? Math.min(100, (asignado / total) * 100) : 0;
        document.getElementById('splitProgressFill').style.width = pct + '%';

        // Vuelto efectivo
        var pagos = [];
        if (nequi > 0)    pagos.push({ metodo: 'NEQUI',    monto: nequi });
        if (davi > 0)     pagos.push({ metodo: 'DAVIPLATA', monto: davi });
        if (bold > 0)     pagos.push({ metodo: 'BOLD',      monto: bold });
        if (efectivo > 0) pagos.push({ metodo: 'EFECTIVO',  monto: efectivo });

        var vueltoEl = document.getElementById('splitSummaryVuelto');
        if (asignado > total && efectivo > 0) {
            var efectivoNecesario = Math.max(0, total - nequi - davi - bold);
            var vuelto = Math.max(0, efectivo - efectivoNecesario);
            if (vuelto > 0) {
                document.getElementById('splitVueltoDisplay').textContent = '$' + vuelto.toLocaleString('es-CO');
                vueltoEl.style.display = '';
            } else {
                vueltoEl.style.display = 'none';
            }
        } else {
            vueltoEl.style.display = 'none';
        }

        // Habilitar confirmar solo si cubierto y al menos 2 métodos
        document.getElementById('btnConfirmSplit').disabled = !(asignado >= total && pagos.length >= 2);
    }

    // Si el modal se cierra con X sin confirmar, limpiamos el estado del botón
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalSplitPay');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                if (!splitPayActive) {
                    document.getElementById('btnToggleSplit').classList.remove('active');
                }
            });
        }
    });
</script>
@endpush
