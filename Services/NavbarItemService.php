<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\NavbarItem;
use Flute\Core\Database\Entities\Role;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class NavbarItemService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateNavbarItemData(array $data): bool
    {
        $rules = [
            'title' => 'required|string|min-str-len:1|max-str-len:255',
            'url' => 'nullable|string|max-str-len:500',
            'new_tab' => 'boolean',
            'icon' => 'nullable|string|max-str-len:100',
            'position' => 'integer|min:0',
            'visible_only_for_guests' => 'boolean',
            'visible_only_for_logged_in' => 'boolean',
            'visibility' => 'string|in:all,desktop,mobile',
            'parent_id' => 'nullable|integer',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllNavbarItems(): array
    {
        return NavbarItem::findAll();
    }

    public function getNavbarItemById(int $id): ?NavbarItem
    {
        return NavbarItem::findByPK($id);
    }

    public function createNavbarItem(array $data): NavbarItem
    {
        if (!$this->validateNavbarItemData($data)) {
            throw new InvalidArgumentException('Invalid navbar item data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $navbarItem = new NavbarItem();
        $this->fillNavbarItem($navbarItem, $data);

        $navbarItem->saveOrFail();

        if (!empty($data['parent_id'])) {
            $parent = $this->getNavbarItemById($data['parent_id']);
            if ($parent) {
                $parent->addChild($navbarItem);
                $parent->saveOrFail();
            }
        }

        return $navbarItem;
    }

    public function updateNavbarItem(NavbarItem $navbarItem, array $data): void
    {
        if (!$this->validateNavbarItemData($data)) {
            throw new InvalidArgumentException('Invalid navbar item data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $oldParent = $navbarItem->parent;

        $this->fillNavbarItem($navbarItem, $data);

        if (isset($data['parent_id'])) {
            if (empty($data['parent_id'])) {
                if ($oldParent) {
                    $oldParent->removeChild($navbarItem);
                    $oldParent->saveOrFail();
                }
                $navbarItem->parent = null;
            } else {
                $newParent = $this->getNavbarItemById($data['parent_id']);

                if ($newParent && (!$oldParent || $oldParent->id !== $newParent->id)) {
                    if ($oldParent) {
                        $oldParent->removeChild($navbarItem);
                        $oldParent->saveOrFail();
                    }

                    $newParent->addChild($navbarItem);
                    $newParent->saveOrFail();
                }
            }
        }

        $navbarItem->saveOrFail();
    }

    public function deleteNavbarItem(NavbarItem $navbarItem): void
    {
        if ($navbarItem->parent) {
            $navbarItem->parent->removeChild($navbarItem);
            $navbarItem->parent->saveOrFail();
        }

        foreach ($navbarItem->children as $child) {
            $child->parent = null;
            $child->saveOrFail();
        }

        $navbarItem->delete();
    }

    public function updateNavbarItemRoles(NavbarItem $navbarItem, array $roleIds): void
    {
        $navbarItem->clearRoles();

        foreach ($roleIds as $id) {
            $role = Role::findByPK($id);
            if (!$role) {
                throw new InvalidArgumentException("Role with ID {$id} not found");
            }
            $navbarItem->addRole($role);
        }

        $navbarItem->saveOrFail();
    }

    public function reorderNavbarItems(array $orderData): void
    {
        foreach ($orderData as $itemData) {
            $id = $itemData['id'] ?? null;
            $position = $itemData['position'] ?? null;

            if (!$id || !is_numeric($position)) {
                throw new InvalidArgumentException('Invalid reorder data. Each item must have id and position.');
            }

            $navbarItem = $this->getNavbarItemById((int)$id);

            if (!$navbarItem) {
                throw new InvalidArgumentException("Navbar item with ID {$id} not found");
            }

            $navbarItem->position = (int)$position;
            $navbarItem->saveOrFail();
        }
    }

    public function formatNavbarItemData(NavbarItem $navbarItem, bool $detailed = false): array
    {
        $data = [
            'id' => $navbarItem->id,
            'title' => $navbarItem->title,
            'url' => $navbarItem->url,
            'new_tab' => $navbarItem->new_tab,
            'icon' => $navbarItem->icon,
            'position' => $navbarItem->position,
            'visible_only_for_guests' => $navbarItem->visibleOnlyForGuests,
            'visible_only_for_logged_in' => $navbarItem->visibleOnlyForLoggedIn,
            'visibility' => $navbarItem->visibility,
            'parent_id' => $navbarItem->parent ? $navbarItem->parent->id : null,
        ];

        if ($detailed) {
            $data['roles'] = array_map(static fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ], $navbarItem->roles);

            $data['children'] = array_map(fn ($child) => $this->formatNavbarItemData($child), $navbarItem->children);
        }

        return $data;
    }

    private function fillNavbarItem(NavbarItem $navbarItem, array $data): void
    {
        $navbarItem->title = $data['title'];
        $navbarItem->url = $data['url'] ?? null;
        $navbarItem->new_tab = $data['new_tab'] ?? false;
        $navbarItem->icon = $data['icon'] ?? null;
        $navbarItem->position = $data['position'] ?? 0;
        $navbarItem->visibleOnlyForGuests = $data['visible_only_for_guests'] ?? false;
        $navbarItem->visibleOnlyForLoggedIn = $data['visible_only_for_logged_in'] ?? false;
        $navbarItem->visibility = $data['visibility'] ?? 'all';
    }
}
