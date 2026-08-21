<?php
require_once __DIR__ . '/../config/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $q = trim((string)($_GET['q'] ?? ''));

        if ($q !== '') {
            $like = "%{$q}%";
            $stmt = db()->prepare(
                'SELECT id, name, brand, price, image_url, description, stock, created_at, updated_at
                 FROM products
                 WHERE name LIKE ? OR brand LIKE ?
                 ORDER BY id DESC'
            );
            $stmt->execute([$like, $like]);
        } else {
            $stmt = db()->query(
                'SELECT id, name, brand, price, image_url, description, stock, created_at, updated_at
                 FROM products ORDER BY id DESC'
            );
        }

        jsonResponse(['success' => true, 'products' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        requireAdmin();
        $data = inputJson();

        $name = trim((string)($data['name'] ?? ''));
        $brand = trim((string)($data['brand'] ?? ''));
        $price = (float)($data['price'] ?? 0);
        $imageUrl = trim((string)($data['image_url'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $stock = (int)($data['stock'] ?? 0);

        if ($name === '' || $brand === '' || $price < 0 || $stock < 0) {
            jsonResponse(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.'], 422);
        }

        $stmt = db()->prepare(
            'INSERT INTO products (name, brand, price, image_url, description, stock)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $brand, $price, $imageUrl, $description, $stock]);

        jsonResponse([
            'success' => true,
            'message' => 'Đã thêm sản phẩm.',
            'id' => (int)db()->lastInsertId()
        ], 201);
    }

    if ($method === 'PUT') {
        requireAdmin();
        $data = inputJson();

        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $brand = trim((string)($data['brand'] ?? ''));
        $price = (float)($data['price'] ?? 0);
        $imageUrl = trim((string)($data['image_url'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $stock = (int)($data['stock'] ?? 0);

        if ($id <= 0 || $name === '' || $brand === '' || $price < 0 || $stock < 0) {
            jsonResponse(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.'], 422);
        }

        $stmt = db()->prepare(
            'UPDATE products
             SET name = ?, brand = ?, price = ?, image_url = ?, description = ?, stock = ?
             WHERE id = ?'
        );
        $stmt->execute([$name, $brand, $price, $imageUrl, $description, $stock, $id]);

        jsonResponse(['success' => true, 'message' => 'Đã cập nhật sản phẩm.']);
    }

    if ($method === 'DELETE') {
        requireAdmin();
        $data = inputJson();
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'], 422);
        }

        $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);

        jsonResponse(['success' => true, 'message' => 'Đã xóa sản phẩm.']);
    }

    jsonResponse(['success' => false, 'message' => 'Method không được hỗ trợ.'], 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Lỗi máy chủ hoặc cơ sở dữ liệu.'
    ], 500);
}
