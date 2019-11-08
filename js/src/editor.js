/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { ServerSideRender } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType( 'amp-stats-block/amp-statistics-block', {
    title: __( 'AMP Validation Statistics', 'block-extend' ),
    icon: 'format-aside',
    category: 'common',
    /*
     * Draws statistics from server.
     *
     * @param object The component properties
     * @return null
     */
    edit( props ) {
        return (
            <ServerSideRender
                block="amp-stats-block/amp-statistics-block"
                attributes={ props.attributes }
            />
        );
    },

    /*
     * This block rendered on server side.
     *
     * @return null
     */
    save() {
        return null;
    },
} );
