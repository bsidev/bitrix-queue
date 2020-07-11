import Vue from 'vue';
import {
    Col,
    Row,
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem
} from 'element-ui';
import lang from 'element-ui/lib/locale/lang/ru-RU';
import locale from 'element-ui/lib/locale';

locale.use(lang);

Vue.prototype.$ELEMENT = { size: 'small' };
Vue.use(Row);
Vue.use(Col);
Vue.use(Button);
Vue.use(Dropdown);
Vue.use(DropdownMenu);
Vue.use(DropdownItem);

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
                render(h) {
                    return h(views[node.dataset['name']], {
                        props: { initial: initialData }
                    });
                }
            });
        }
    });
});