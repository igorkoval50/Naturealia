<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Blog;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Storefront\Page\Page;

class BlogPage extends Page
{
    protected $post;

    protected $cmsPage;

    public function getPost(): BlogEntity
    {
        return $this->post;
    }

    public function setPost(BlogEntity $post): void
    {
        $this->post = $post;
    }

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }
}
