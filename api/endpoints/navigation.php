<?php

class NavigationEndpoints {
    private $db;
    private $hasTable;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureSchema();
        $this->hasTable = $this->tableExists('navigation_menu_items');
    }

    public function handle($segments, $method) {
        $sub = isset($segments[1]) ? $segments[1] : '';

        switch ($sub) {
            case 'menu-items':
                $this->handleMenuItems($segments, $method);
                break;
            case 'header-menu':
                $this->handleHeaderMenu();
                break;
            default:
                errorResponse('Not Found', 404);
        }
    }

    private function handleMenuItems($segments, $method) {
        if (!$this->hasTable) {
            errorResponse('Navigation table not configured', 501);
        }

        $id = isset($segments[2]) ? $segments[2] : '';
        $action = isset($segments[3]) ? $segments[3] : '';

        switch ($method) {
            case 'GET':
                $this->listMenuItems();
                break;
            case 'POST':
                AuthMiddleware::requireEditorOrAdmin();
                $this->createMenuItem();
                break;
            case 'PUT':
                AuthMiddleware::requireEditorOrAdmin();
                if ($id === '') {
                    errorResponse('ID is required', 400);
                }
                $this->updateMenuItem($id);
                break;
            case 'PATCH':
                AuthMiddleware::requireEditorOrAdmin();
                if ($id === '' || $action !== 'toggle') {
                    errorResponse('Not Found', 404);
                }
                $this->toggleMenuItem($id);
                break;
            case 'DELETE':
                AuthMiddleware::requireEditorOrAdmin();
                if ($id === '') {
                    errorResponse('ID is required', 400);
                }
                $this->deleteMenuItem($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function handleHeaderMenu() {
        if ($this->hasTable) {
            $rows = $this->db->fetchAll(
                "SELECT * FROM navigation_menu_items ORDER BY position ASC, title ASC"
            );
            $rows = $rows ? $rows : [];
            $items = array_map([$this, 'formatMenuItem'], $rows);
            $items = array_values(array_filter($items));
            $tree = $this->buildTree($items, true);
            jsonResponse(['navigation' => $tree]);
            return;
        }

        // Fallback: build navigation from pages table if available
        $pages = [];
        if ($this->tableExists('pages')) {
            $pages = $this->db->fetchAll(
                "SELECT id, title, slug FROM pages WHERE is_active = 1 ORDER BY position ASC, title ASC"
            );
        }

        $navigation = array_map(function($page) {
            $slug = isset($page['slug']) ? $page['slug'] : '';
            $path = '/' . ltrim($slug, '/');
            if ($path === '//') {
                $path = '/';
            }
            return [
                'id' => isset($page['id']) ? (string)$page['id'] : uniqid('page_', true),
                'title' => isset($page['title']) ? $page['title'] : 'Page',
                'path' => $path,
                'position' => isset($page['position']) ? (int)$page['position'] : 0,
                'is_active' => true,
                'children' => []
            ];
        }, $pages);

        if (empty($navigation)) {
            $navigation = [
                [
                    'id' => 'home',
                    'title' => 'Начало',
                    'path' => '/',
                    'position' => 0,
                    'is_active' => true,
                    'children' => []
                ]
            ];
        }

        jsonResponse(['navigation' => $navigation]);
    }

    private function listMenuItems() {
        $rows = $this->db->fetchAll(
            "SELECT * FROM navigation_menu_items ORDER BY position ASC, title ASC"
        );
        $rows = $rows ? $rows : [];
        $items = array_map([$this, 'formatMenuItem'], $rows);
        $items = array_values(array_filter($items));
        $tree = $this->buildTree($items, false);

        jsonResponse([
            'items' => $items,
            'tree' => $tree,
            'total' => count($items)
        ]);
    }

    private function createMenuItem() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            errorResponse('Invalid payload', 400);
        }

        if (!isset($input['title']) || trim($input['title']) === '') {
            errorResponse('Title is required', 400);
        }

        if (!isset($input['path']) || trim($input['path']) === '') {
            errorResponse('Path is required', 400);
        }

        $id = isset($input['id']) && $input['id'] !== '' ? $input['id'] : uniqid('nav_', true);

        $this->db->insert('navigation_menu_items', [
            'id' => $id,
            'title' => $input['title'],
            'path' => $input['path'],
            'parent_id' => isset($input['parentId']) && $input['parentId'] !== '' ? $input['parentId'] : null,
            'position' => isset($input['position']) ? (int)$input['position'] : 0,
            'is_active' => isset($input['isActive']) ? (int)!!$input['isActive'] : 1,
            'icon' => isset($input['icon']) ? $input['icon'] : null,
            'css_class' => isset($input['cssClass']) ? $input['cssClass'] : null
        ]);

        $row = $this->db->fetchOne(
            "SELECT * FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('Failed to load created navigation item', 500);
        }

        jsonResponse(['item' => $this->formatMenuItem($row)], 201);
    }

    private function updateMenuItem($id) {
        $existing = $this->db->fetchOne(
            "SELECT * FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        if (!$existing) {
            errorResponse('Navigation item not found', 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            errorResponse('Invalid payload', 400);
        }

        $data = [];

        if (isset($input['title'])) {
            $data['title'] = $input['title'];
        }
        if (isset($input['path'])) {
            $data['path'] = $input['path'];
        }
        if (array_key_exists('parentId', $input)) {
            $data['parent_id'] = $input['parentId'] !== '' ? $input['parentId'] : null;
        }
        if (isset($input['position'])) {
            $data['position'] = (int)$input['position'];
        }
        if (isset($input['isActive'])) {
            $data['is_active'] = (int)!!$input['isActive'];
        }
        if (array_key_exists('icon', $input)) {
            $data['icon'] = $input['icon'];
        }
        if (array_key_exists('cssClass', $input)) {
            $data['css_class'] = $input['cssClass'];
        }

        if (empty($data)) {
            jsonResponse(['item' => $this->formatMenuItem($existing)]);
        }

        $this->db->update(
            'navigation_menu_items',
            $data,
            'id = :id',
            ['id' => $id]
        );

        $row = $this->db->fetchOne(
            "SELECT * FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('Navigation item not found', 404);
        }

        jsonResponse(['item' => $this->formatMenuItem($row)]);
    }

    private function toggleMenuItem($id) {
        $item = $this->db->fetchOne(
            "SELECT is_active FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        if (!$item) {
            errorResponse('Navigation item not found', 404);
        }

        $newStatus = isset($item['is_active']) ? ((int)!$item['is_active']) : 1;

        $this->db->update(
            'navigation_menu_items',
            ['is_active' => $newStatus],
            'id = :id',
            ['id' => $id]
        );

        jsonResponse(['id' => $id, 'isActive' => (bool)$newStatus]);
    }

    private function deleteMenuItem($id) {
        $deleted = $this->db->delete(
            'navigation_menu_items',
            'id = ?',
            [$id]
        );

        if ($deleted === 0) {
            errorResponse('Navigation item not found', 404);
        }

        $this->seedDefaultItems();

        jsonResponse(['message' => 'Navigation item deleted successfully']);
    }

    private function buildTree(array &$items, $skipInactive = false) {
        $indexed = [];
        foreach ($items as &$item) {
            if (!isset($item['children']) || !is_array($item['children'])) {
                $item['children'] = [];
            }
            $indexed[$item['id']] = &$item;
        }
        unset($item);

        $tree = [];
        foreach ($indexed as $id => &$item) {
            if ($skipInactive && isset($item['isActive']) && !$item['isActive']) {
                continue;
            }

            $parentId = isset($item['parentId']) ? $item['parentId'] : null;
            if ($parentId && isset($indexed[$parentId])) {
                $indexed[$parentId]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }
        unset($item);

        // Remove references
        foreach ($indexed as &$linked) {
            if (isset($linked['children'])) {
                $linked['children'] = array_values($linked['children']);
            }
        }
        unset($linked);

        return array_values($tree);
    }

    private function tableExists($table) {
        try {
            $count = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
                [$table]
            );
            return ((int) $count) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function ensureSchema() {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS navigation_menu_items (
                id VARCHAR(191) NOT NULL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                path VARCHAR(500) NOT NULL,
                parent_id VARCHAR(191) NULL,
                position INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                icon VARCHAR(100) NULL,
                css_class VARCHAR(100) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_parent (parent_id),
                INDEX idx_position (position),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->seedDefaultItems();
    }

    private function seedDefaultItems() {
        $defaults = [
            [
                'id' => 'documents',
                'title' => 'Documents',
                'path' => '/documents',
                'position' => 20,
            ],
            [
                'id' => 'projects',
                'title' => 'Projects',
                'path' => '/projects',
                'position' => 30,
            ],
        ];

        foreach ($defaults as $item) {
            $exists = $this->db->fetchOne(
                "SELECT id FROM navigation_menu_items WHERE id = ?",
                [$item['id']]
            );

            if (!$exists) {
                $this->db->insert('navigation_menu_items', [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'path' => $item['path'],
                    'parent_id' => null,
                    'position' => $item['position'],
                    'is_active' => 1,
                    'icon' => null,
                    'css_class' => null
                ]);
            }
        }
    }

    private function formatMenuItem($row) {
        if (!$row) {
            return null;
        }

        $item = [
            'id' => isset($row['id']) ? (string)$row['id'] : uniqid('nav_', true),
            'title' => isset($row['title']) ? $row['title'] : '',
            'path' => isset($row['path']) ? $row['path'] : '/',
            'parentId' => isset($row['parent_id']) && $row['parent_id'] !== '' ? (string)$row['parent_id'] : null,
            'position' => isset($row['position']) ? (int)$row['position'] : 0,
            'isActive' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
            'icon' => isset($row['icon']) ? $row['icon'] : null,
            'cssClass' => isset($row['css_class']) ? $row['css_class'] : null,
        ];

        if (isset($row['children']) && is_array($row['children'])) {
            $item['children'] = array_map([$this, 'formatMenuItem'], $row['children']);
        } else {
            $item['children'] = isset($row['children']) ? $row['children'] : [];
        }

        return $item;
    }
}
