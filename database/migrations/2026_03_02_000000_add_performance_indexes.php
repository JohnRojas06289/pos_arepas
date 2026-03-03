<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds missing indexes to tables that are frequently queried.
 *
 * In PostgreSQL, foreign key constraints do NOT automatically create an index
 * on the referencing column, unlike MySQL/InnoDB. Every join or WHERE clause
 * on a FK column that lacks its own index causes a sequential scan.
 *
 * Indexes added:
 *  - ventas.created_at            → dashboard date-range SUM queries
 *  - ventas.(user_id, created_at) → per-user + date compound queries
 *  - kardex.producto_id           → kardex lookup by product (FK not indexed)
 *  - kardex.(producto_id, created_at) → latest kardex record per product
 *  - producto_venta.producto_id   → aggregate sales by product
 *  - producto_venta.venta_id      → load line-items for a sale
 *  - compra_producto.producto_id  → aggregate purchases by product
 *  - compra_producto.compra_id    → load line-items for a purchase
 *  - cajas.(user_id, estado)      → check-caja-aperturada-user middleware
 */
return new class extends Migration
{
    public function up(): void
    {
        // ventas ----------------------------------------------------------------
        Schema::table('ventas', function (Blueprint $table) {
            $table->index('created_at', 'idx_ventas_created_at');
            $table->index(['user_id', 'created_at'], 'idx_ventas_user_date');
        });

        // kardex ----------------------------------------------------------------
        Schema::table('kardex', function (Blueprint $table) {
            $table->index('producto_id', 'idx_kardex_producto_id');
            $table->index(['producto_id', 'created_at'], 'idx_kardex_producto_date');
        });

        // producto_venta --------------------------------------------------------
        Schema::table('producto_venta', function (Blueprint $table) {
            $table->index('producto_id', 'idx_pv_producto_id');
            $table->index('venta_id', 'idx_pv_venta_id');
        });

        // compra_producto -------------------------------------------------------
        Schema::table('compra_producto', function (Blueprint $table) {
            $table->index('producto_id', 'idx_cp_producto_id');
            $table->index('compra_id', 'idx_cp_compra_id');
        });

        // cajas -----------------------------------------------------------------
        Schema::table('cajas', function (Blueprint $table) {
            $table->index(['user_id', 'estado'], 'idx_cajas_user_estado');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_created_at');
            $table->dropIndex('idx_ventas_user_date');
        });

        Schema::table('kardex', function (Blueprint $table) {
            $table->dropIndex('idx_kardex_producto_id');
            $table->dropIndex('idx_kardex_producto_date');
        });

        Schema::table('producto_venta', function (Blueprint $table) {
            $table->dropIndex('idx_pv_producto_id');
            $table->dropIndex('idx_pv_venta_id');
        });

        Schema::table('compra_producto', function (Blueprint $table) {
            $table->dropIndex('idx_cp_producto_id');
            $table->dropIndex('idx_cp_compra_id');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropIndex('idx_cajas_user_estado');
        });
    }
};
