import Vue from 'vue';

import './plugins/element-ui';
import i18n from './i18n';

import views from './views';
import './styles/index.scss';

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded', () => {
    const nodes = Array.from(document.querySelectorAll('.vue-shell'));
    nodes.forEach(node => {
        let initialData = node.dataset['initial'];
        if (initialData !== undefined) {
            try {
                initialData = JSON.parse(initialData);
            } catch (e) {
                console.warn(e);
            }
        }

        if (views[node.dataset['name']] !== undefined) {
            new Vue({
                el: node,
                i18n,
                render(h) {
                    return h(views[node.dataset['name']], {
                        props: { initial: initialData }
                    });
                }
            });
        }
    });
});