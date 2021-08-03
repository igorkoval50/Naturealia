<?php declare(strict_types=1);

namespace NetzpBlog6;

use NetzpBlog6\Core\Content\Blog\BlogDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Doctrine\DBAL\Connection;

class NetzpBlog6 extends Plugin
{
    public const BLOG_MEDIAFOLDER_NAME = 'Blog Media';
    public const BLOG_MEDIAFOLDER_ID = '59D4F5B90E944D44B997ED0A60804034';

    public function install(InstallContext $context): void
    {
        $this->createMediaFolder($context->getContext());
    }

    public function update(UpdateContext $context): void
    {
        if (version_compare($context->getCurrentPluginVersion(), '1.1.0', '<')) {
            $this->createMediaFolder($context->getContext());
        }
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);
        if ($context->keepUserData()) {
            return;
        }

        $this->removeMediaFolder($context->getContext());
        $this->removeMigrations();

        $connection = $this->container->get(Connection::class);
        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_categories`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_product`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_category_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_category`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_author_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_author`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_tag`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_item_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_item`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_media`');

        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `s_plugin_netzp_blog`');

        try {
            $connection->executeUpdate('ALTER TABLE `product` DROP COLUMN `blogs`');
        }
        catch (\Exception $ex) { }

        try {
            $connection->executeUpdate('DELETE FROM `seo_url_template` WHERE route_name = "frontend.blog.post"');
        }
        catch (\Exception $ex) {
            //
        }
    }

    private function removeMediaFolder(Context $context): void
    {
        $connection = $this->container->get(Connection::class);
        try {
            $defaultFolderId = $connection->fetchColumn('SELECT HEX(id) FROM media_default_folder WHERE HEX(id) = ?', [
                strtolower(self::BLOG_MEDIAFOLDER_ID)
            ]);
            if( ! $defaultFolderId) return;

            $defaultConfigurationId = $connection->fetchColumn('SELECT HEX(media_folder_configuration_id) FROM media_folder WHERE HEX(default_folder_id) = ?', [
                $defaultFolderId
            ]);
            if( ! $defaultConfigurationId) return;

            $connection->executeUpdate('DELETE FROM `media_folder_configuration` WHERE HEX(id) = ?', [
                $defaultConfigurationId
            ]);
            $connection->executeUpdate('DELETE FROM `media_folder` WHERE HEX(default_folder_id) = ?', [
                $defaultFolderId
            ]);
            $connection->executeUpdate('DELETE FROM `media_default_folder` WHERE HEX(id) = ?', [
                strtolower(self::BLOG_MEDIAFOLDER_ID)
            ]);
        }
        catch (\Exception $ex) {
            //
        }
    }

    public function createMediaFolder(Context $context): void
    {
        try {
            $connection = $this->container->get(Connection::class);
            $thumbnailIds = $connection->fetchAll('SELECT LOWER(HEX(id)) AS id from `media_thumbnail_size` WHERE width in (400, 800, 1920)');

            $repo = $this->container->get('media_default_folder.repository');
            $repo->upsert([
                [
                    'id'                => strtolower(self::BLOG_MEDIAFOLDER_ID),
                    'entity'            => BlogDefinition::ENTITY_NAME,
                    'associationFields' => ['media'],
                    'folder'            => [
                        'name'                   => self::BLOG_MEDIAFOLDER_NAME,
                        'useParentConfiguration' => false,
                        'configuration'          => [
                            'createThumbnails'    => true,
                            'mediaThumbnailSizes' => $thumbnailIds
                        ]
                    ]
                ]
            ], $context);
        }
        catch (\Exception $ex) {
            //
        }
    }
}
