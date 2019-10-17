import { registerBlockType } from '@wordpress/blocks';
import { lodash } from 'lodash';
 
const blockStyle = {
    backgroundColor: '#900',
    color: '#fff',
    padding: '20px',
};
 
registerBlockType( 'idx-broker-platinum/test-block', {
    title: 'Example: Basic (esnext)',
    icon: 'universal-access-alt',
    category: 'layout',
    example: {},
    edit() {
        return <div style={ blockStyle }>Hello World, step 1 (from the editor).</div>;
    },
    save() {
        return <div style={ blockStyle }>Hello World, step 1 (from the frontend).</div>;
    },
} );