<?php
/**
 * Handle the metabox on edit post/page.
 */
class LinkGist_MetaBox
{
    /**
     * Assign MetaBox Hooks.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'addMetaBox'));
        add_action('save_post', array($this, 'save'));
    }

    /**
     * Add the meta box container.
     *
     * @param  string  $postType
     * @return void
     */
    public function addMetaBox($postType)
    {
        add_meta_box(
            'linkgist_meta_box',
            'LinkGist',
            array($this, 'render'),
            $postType,
            'side',
            'low'
        );
    }

    /**
     * Render the meta box content.
     *
     * @param  WP_Post  $post
     * @return void
     */
    public function render($post)
    {
        wp_nonce_field('linkgist_metabox', 'linkgist_metabox_nonce' );
        $value = get_post_meta($post->ID, '_linkgist_disable', true);
        $options = get_option(LinkGist::OPTION_KEY);
        $mediaId = isset($options['media_id']) ? $options['media_id'] : '';
        $monetize = __('monetizing the links', LinkGist::TEXT_DOMAIN);

        printf(
            '<p>%s</p>',
            sprintf(
                __(
                    'Congrats, you\'ve successfully installed LinkGist! LinkGist is a smart way of %s in your blog.',
                    LinkGist::TEXT_DOMAIN
                ),
                $mediaId ? sprintf('<a href="https://www.linkgist.com/customer/mediastats/hits/id/%s">%s</a>', $mediaId, $monetize) : $monetize
            )
        );

        printf('<p><label for="%1$s"><input name="%1$s" type="checkbox" id="%1$s" value="1" %2$s/>%3$s</label></p>',
            'linkgist_disable',
            $value ? 'checked="checked"' : '',
            __('Disable for this entry', LinkGist::TEXT_DOMAIN)
        );
    }

    /**
     * Save custom meta data when post is saved.
     *
     * @param  int  $postId
     */
    public function save($postId)
    {
        // Check Nonce
        if (!isset($_POST['linkgist_metabox_nonce'])) {
            return $postId;
        }

        $nonce = $_POST['linkgist_metabox_nonce'];
        if (!wp_verify_nonce($nonce, 'linkgist_metabox')) {
            return $postId;
        }

        // If this is an autosave, we don't want to do anything
        if (defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
            return $postId;
        }

        // Check user permission
        if (!current_user_can('edit_post', $postId)) {
            return $postId;
        }

        // Time to set the data
        if (isset($_POST['linkgist_disable'])) {
            update_post_meta($postId, '_linkgist_disable', true);
        } else {
            delete_post_meta($postId, '_linkgist_disable');
        }
    }
}
