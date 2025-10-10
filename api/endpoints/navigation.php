<?php

class NavigationEndpoints {
    private $db;
    private $hasTable;

    public function __construct() {
        $this->db = Database::getInstance();
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
            $items = $this->db->fetchAll(
                "SELECT * FROM navigation_menu_items ORDER BY position ASC, title ASC"
            );
            $items = $items ? $items : [];
            $tree = $this->buildTree($items);
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
        $items = $this->db->fetchAll(
            "SELECT * FROM navigation_menu_items ORDER BY position ASC, title ASC"
        );
        $items = $items ? $items : [];

        jsonResponse([
            'items' => $items,
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

        $item = $this->db->fetchOne(
            "SELECT * FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        jsonResponse($item, 201);
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
            jsonResponse($existing);
        }

        $this->db->update(
            'navigation_menu_items',
            $data,
            'id = :id',
            ['id' => $id]
        );

        $item = $this->db->fetchOne(
            "SELECT * FROM navigation_menu_items WHERE id = ?",
            [$id]
        );

        jsonResponse($item);
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

        jsonResponse(['id' => $id, 'is_active' => (bool)$newStatus]);
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

        jsonResponse(['message' => 'Navigation item deleted successfully']);
    }

    private function buildTree($items) {
        $indexed = [];
        foreach ($items as $item) {
            $item['children'] = [];
            $indexed[$item['id']] = $item;
        }

        $tree = [];
        foreach ($indexed as $id => &$item) {
            if (isset($item['is_active']) && !(int)$item['is_active']) {
                continue;
            }

            $parentId = isset($item['parent_id']) ? $item['parent_id'] : null;
            if ($parentId && isset($indexed[$parentId])) {
                $indexed[$parentId]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        // Remove references
        unset($item);

        return $tree;
    }

    private function tableExists($table) {
        try {
            $result = $this->db->fetchOne("SHOW TABLES LIKE ?", [$table]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
}
