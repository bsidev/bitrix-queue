import Vue from 'vue';

Vue.config.productionTip = false;

import views from './views';
import './styles/index.scss';

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
                render(h) {
                    return h(views[node.dataset['name']], {
                        props: { initial: initialData }
                    });
                }
            });
        }
    });
});