<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. BUAT TABEL AUDIT_LOGS UNTUK MENAMPUNG SEMUA RIWAYAT
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);          // Contoh: orders, order_items, produk, users
            $table->string('action', 10);               // INSERT, UPDATE, DELETE
            $table->unsignedBigInteger('record_id');   // ID dari data yang diproses
            $table->json('old_values')->nullable();    // Data sebelum diubah / sebelum dihapus
            $table->json('new_values')->nullable();    // Data setelah diinsert / setelah diubah
            $table->timestamp('created_at')->useCurrent();

            $table->index(['table_name', 'record_id']);
            $table->index('action');
        });

        // =========================================================================
        // 2. TRIGGERS UNTUK TABEL: ORDERS (KRUSIAL TRANSAKSI)
        // =========================================================================

        // INSERT ORDERS
        DB::unprepared("
            CREATE TRIGGER trg_orders_after_insert
            AFTER INSERT ON orders
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('orders', 'INSERT', NEW.id, NULL, JSON_OBJECT(
                    'order_number', NEW.order_number,
                    'customer_name', NEW.customer_name,
                    'customer_email', NEW.customer_email,
                    'customer_phone', NEW.customer_phone,
                    'order_type', NEW.order_type,
                    'status', NEW.status,
                    'payment_method', NEW.payment_method,
                    'payment_plan', NEW.payment_plan,
                    'payment_status', NEW.payment_status,
                    'total_amount', NEW.total_amount,
                    'dp_amount', NEW.dp_amount,
                    'amount_paid', NEW.amount_paid,
                    'fulfill_at', NEW.fulfill_at
                ), NOW());
            END
        ");

        // UPDATE ORDERS
        DB::unprepared("
            CREATE TRIGGER trg_orders_after_update
            AFTER UPDATE ON orders
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('orders', 'UPDATE', NEW.id,
                JSON_OBJECT(
                    'status', OLD.status,
                    'payment_status', OLD.payment_status,
                    'amount_paid', OLD.amount_paid,
                    'payment_proof', OLD.payment_proof,
                    'fulfill_at', OLD.fulfill_at
                ),
                JSON_OBJECT(
                    'status', NEW.status,
                    'payment_status', NEW.payment_status,
                    'amount_paid', NEW.amount_paid,
                    'payment_proof', NEW.payment_proof,
                    'fulfill_at', NEW.fulfill_at
                ), NOW());
            END
        ");

        // DELETE ORDERS (PENYELAMAT RECOVERY DATA JIKA TERHAPUS PAKSA/DIRECT DB)
        DB::unprepared("
            CREATE TRIGGER trg_orders_after_delete
            AFTER DELETE ON orders
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('orders', 'DELETE', OLD.id, JSON_OBJECT(
                    'order_number', OLD.order_number,
                    'user_id', OLD.user_id,
                    'customer_name', OLD.customer_name,
                    'customer_phone', OLD.customer_phone,
                    'customer_email', OLD.customer_email,
                    'order_type', OLD.order_type,
                    'delivery_address', OLD.delivery_address,
                    'status', OLD.status,
                    'payment_method', OLD.payment_method,
                    'payment_plan', OLD.payment_plan,
                    'payment_status', OLD.payment_status,
                    'total_amount', OLD.total_amount,
                    'dp_amount', OLD.dp_amount,
                    'amount_paid', OLD.amount_paid,
                    'fulfill_at', OLD.fulfill_at,
                    'notes', OLD.notes,
                    'placed_at', OLD.placed_at
                ), NULL, NOW());
            END
        ");

        // =========================================================================
        // 3. TRIGGERS UNTUK TABEL: ORDER_ITEMS (DETAIL ITEM ROTI/KUE)
        // =========================================================================

        DB::unprepared("
            CREATE TRIGGER trg_order_items_after_insert
            AFTER INSERT ON order_items
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('order_items', 'INSERT', NEW.id, NULL, JSON_OBJECT(
                    'order_id', NEW.order_id,
                    'product_id', NEW.product_id,
                    'product_name', NEW.product_name,
                    'unit_price', NEW.unit_price,
                    'quantity', NEW.quantity,
                    'subtotal', NEW.subtotal
                ), NOW());
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_order_items_after_delete
            AFTER DELETE ON order_items
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('order_items', 'DELETE', OLD.id, JSON_OBJECT(
                    'order_id', OLD.order_id,
                    'product_id', OLD.product_id,
                    'product_name', OLD.product_name,
                    'unit_price', OLD.unit_price,
                    'quantity', OLD.quantity,
                    'subtotal', OLD.subtotal
                ), NULL, NOW());
            END
        ");

        // =========================================================================
        // 4. TRIGGERS UNTUK TABEL: PAYMENTS (SINKRONISASI MIDTRANS/PAYMENT GATEWAY)
        // =========================================================================

        DB::unprepared("
            CREATE TRIGGER trg_payments_after_insert
            AFTER INSERT ON payments
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('payments', 'INSERT', NEW.id, NULL, JSON_OBJECT(
                    'order_id', NEW.order_id,
                    'type', NEW.type,
                    'amount', NEW.amount,
                    'status', NEW.status,
                    'reference', NEW.reference
                ), NOW());
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_payments_after_update
            AFTER UPDATE ON payments
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('payments', 'UPDATE', NEW.id,
                JSON_OBJECT('status', OLD.status, 'paid_at', OLD.paid_at),
                JSON_OBJECT('status', NEW.status, 'paid_at', NEW.paid_at),
                NOW());
            END
        ");

        // =========================================================================
        // 5. TRIGGERS UNTUK TABEL: PRODUK (PRODUK & HARGA KATALOG)
        // =========================================================================

        DB::unprepared("
            CREATE TRIGGER trg_produk_after_update
            AFTER UPDATE ON produk
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('produk', 'UPDATE', NEW.id,
                JSON_OBJECT('nama_produk', OLD.nama_produk, 'harga', OLD.harga, 'status', OLD.status, 'is_available', OLD.is_available),
                JSON_OBJECT('nama_produk', NEW.nama_produk, 'harga', NEW.harga, 'status', NEW.status, 'is_available', NEW.is_available),
                NOW());
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_produk_after_delete
            AFTER DELETE ON produk
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('produk', 'DELETE', OLD.id, JSON_OBJECT(
                    'nama_produk', OLD.nama_produk,
                    'harga', OLD.harga,
                    'category_id', OLD.category_id
                ), NULL, NOW());
            END
        ");

        // =========================================================================
        // 6. TRIGGERS UNTUK TABEL: USERS (AKUN AKAN TERDOKUMENTASI JIKA DIHAPUS)
        // =========================================================================

        DB::unprepared("
            CREATE TRIGGER trg_users_after_delete
            AFTER DELETE ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (table_name, action, record_id, old_values, new_values, created_at)
                VALUES ('users', 'DELETE', OLD.id, JSON_OBJECT(
                    'name', OLD.name,
                    'email', OLD.email,
                    'role', OLD.role,
                    'phone', OLD.phone
                ), NULL, NOW());
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus seluruh Trigger terlebih dahulu
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_delete');

        DB::unprepared('DROP TRIGGER IF EXISTS trg_order_items_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_order_items_after_delete');

        DB::unprepared('DROP TRIGGER IF EXISTS trg_payments_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_payments_after_update');

        DB::unprepared('DROP TRIGGER IF EXISTS trg_produk_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_produk_after_delete');

        DB::unprepared('DROP TRIGGER IF EXISTS trg_users_after_delete');

        // Hapus Tabel audit_logs
        Schema::dropIfExists('audit_logs');
    }
};
