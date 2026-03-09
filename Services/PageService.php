<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\Page;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class PageService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validatePageData(array $data): bool
    {
        $rules = [
            'route' => 'required|string|min-str-len:1|max-str-len:255',
            'title' => 'required|string|min-str-len:3|max-str-len:255',
            'description' => 'nullable|string|max-str-len:1000',
            'keywords' => 'nullable|string|max-str-len:500',
            'robots' => 'nullable|string|max-str-len:100',
            'og_image' => 'nullable|string|max-str-len:500',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllPages(): array
    {
        return cache()->callback('flute.pages.all', static fn () => Page::query()->load('permissions')->fetchAll(), 300);
    }

    public function getPageById(int $id): ?Page
    {
        return Page::findByPK($id);
    }

    public function createPage(array $data): Page
    {
        if (!$this->validatePageData($data)) {
            throw new InvalidArgumentException('Invalid page data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $page = new Page();
        $page->setRoute($data['route']);
        $page->setTitle($data['title']);

        if (isset($data['description'])) {
            $page->setDescription($data['description']);
        }

        if (isset($data['keywords'])) {
            $page->setKeywords($data['keywords']);
        }

        if (isset($data['robots'])) {
            $page->setRobots($data['robots']);
        }

        if (isset($data['og_image'])) {
            $page->setOgImage($data['og_image']);
        }

        $page->saveOrFail();

        return $page;
    }

    public function updatePage(Page $page, array $data): void
    {
        if (!$this->validatePageData($data)) {
            throw new InvalidArgumentException('Invalid page data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $page->setRoute($data['route']);
        $page->setTitle($data['title']);

        if (isset($data['description'])) {
            $page->setDescription($data['description']);
        } else {
            $page->setDescription(null);
        }

        if (isset($data['keywords'])) {
            $page->setKeywords($data['keywords']);
        } else {
            $page->setKeywords(null);
        }

        if (isset($data['robots'])) {
            $page->setRobots($data['robots']);
        } else {
            $page->setRobots(null);
        }

        if (isset($data['og_image'])) {
            $page->setOgImage($data['og_image']);
        } else {
            $page->setOgImage(null);
        }

        $page->saveOrFail();
    }

    public function deletePage(Page $page): void
    {
        $page->delete();
    }

    public function getPageBlocks(Page $page): array
    {
        return array_map(static fn ($block) => [
            'id' => $block->id,
            'type' => $block->type,
            'title' => $block->title,
            'content' => $block->content,
            'order' => $block->order,
            'is_active' => $block->is_active,
        ], $page->getBlocks());
    }

    public function updatePagePermissions(Page $page, array $permissionIds): void
    {
        foreach ($page->permissions as $permission) {
            $page->removePermission($permission);
        }

        foreach ($permissionIds as $id) {
            $permission = \Flute\Core\Database\Entities\Permission::findByPK($id);
            if (!$permission) {
                throw new InvalidArgumentException("Permission with ID {$id} not found");
            }
            $page->addPermission($permission);
        }

        $page->saveOrFail();
    }

    public function formatPageData(Page $page, bool $detailed = false): array
    {
        $data = [
            'id' => $page->getId(),
            'route' => $page->getRoute(),
            'title' => $page->getTitle(),
            'description' => $page->getDescription(),
            'keywords' => $page->getKeywords(),
            'robots' => $page->getRobots(),
            'og_image' => $page->getOgImage(),
        ];

        if ($detailed) {
            $data['blocks'] = $this->getPageBlocks($page);

            $data['permissions'] = array_map(static fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ], $page->getPermissions());
        }

        return $data;
    }
}
