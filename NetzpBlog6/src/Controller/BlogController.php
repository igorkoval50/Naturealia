<?php declare(strict_types=1);

namespace NetzpBlog6\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use NetzpBlog6\Core\Content\Blog\BlogEntity;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Shopware\Storefront\Page\MetaInformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NetzpBlog6\Core\Content\Blog\BlogPage;
use NetzpBlog6\Helper\BlogHelper;

/**
 * @RouteScope(scopes={"storefront"})
 */
class BlogController extends StorefrontController
{
    protected $container;
    private $helper;
    private $pageLoader;
    private $cmsPageLoader;
    private $config;

    public function __construct(
        ContainerInterface $container,
        BlogHelper $helper,
        GenericPageLoaderInterface $pageLoader,
        SalesChannelCmsPageLoaderInterface $cmsPageLoader,
        SystemConfigService $config)
    {
        $this->container = $container;
        $this->helper = $helper;
        $this->pageLoader = $pageLoader;
        $this->cmsPageLoader = $cmsPageLoader;
        $this->config = $config;
    }

    /**
     * @Route("/blog.rss", name="frontend.blog.feed", methods={"GET"})
     */
    public function getFeed(Request $request, SalesChannelContext $salesChannelContext, Context $context)
    {
        $posts = $this->helper->getPublicBlogPosts($salesChannelContext, $context, null, null, true);

        $response = $this->renderStorefront('@Storefront/storefront/page/blog/feed.html.twig', [
            'posts' => $posts
        ]);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/blog/{postId}", name="frontend.blog.post", methods={"GET"})
     * @throws PageNotFoundException
     */
    public function getPost(Request $request, SalesChannelContext $salesChannelContext, Context $context, $postId)
    {
        $post = $this->helper->getBlogPost($postId, $salesChannelContext, $context);
        if(! $post) {
            throw new NotFoundHttpException('Blog post not found');
        }

        $shariffIsActive = $this->helper->isPluginActive('NetzpShariff6', $context);
        $config = $this->config->get('NetzpBlog6.config', $salesChannelContext->getSalesChannel()->getId());

        $cmsPageId = null;
        $category = $post->getCategory();
        if($category && $category->getCmspageid()) {
            $cmsPageId = $category->getCmspageid();
        }
        else {
            $cmsPageId = array_key_exists('cmspage', $config) && $config['cmspage'] !== '' ? $config['cmspage'] : null;
        }

        if($cmsPageId !== null || $cmsPageId != '') {
            $page = $this->loadCmsPage($request, $salesChannelContext, $cmsPageId, $post);
            $page->setPost($post);
            $template = '@Storefront/storefront/page/content/index.html.twig';
        }
        else {
            $page = $this->loadNormalPage($request, $salesChannelContext, $post);
            $template = '@Storefront/storefront/page/blog/post.html.twig';
        }

        return $this->renderStorefront($template, [
            'page'               => $page,
            'post'               => $post,
            'config'             => $config,
            'netzpShariffActive' => $shariffIsActive
        ]);
    }

    private function loadCmsPage(Request $request, SalesChannelContext $salesChannelContext,
                                 $cmsPageId, BlogEntity $post)
    {
        $cmsPage = $this->cmsPageLoader->load($request, new Criteria([$cmsPageId]), $salesChannelContext)->first();
        $page = BlogPage::createFrom($this->pageLoader->load($request, $salesChannelContext));
        $page->setMetaInformation($this->getMetaInformation($post));
        $page->setCmsPage($cmsPage);

        return $page;
    }

    private function loadNormalPage(Request $request, SalesChannelContext $salesChannelContext,
                                    BlogEntity $post)
    {
        $page = $this->pageLoader->load($request, $salesChannelContext);
        $page->setMetaInformation($this->getMetaInformation($post));

        return $page;
    }

    private function getMetaInformation(BlogEntity $post)
    {
        $meta = new MetaInformation();

        $meta->setMetaTitle($post->getTranslation('metatitle') ?? '');
        $meta->setMetaDescription($post->getTranslation('metadescription') ?? '');
        if ($post->getAuthor()) {
            $meta->setAuthor($post->getAuthor()->getTranslation('name') ?? '');
        }
        if ($post->getNoindex()) {
            $meta->setRobots('noindex, nofollow');
        }

        return $meta;
    }
}
