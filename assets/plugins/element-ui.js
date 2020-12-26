import Vue from 'vue';
import {
    Col,
    Row,
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    Pagination,
    Tag,
    Drawer,
    Input,
    Select,
    Option
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
Vue.use(Pagination);
Vue.use(Tag);
Vue.use(Drawer);
Vue.use(Input);
Vue.use(Select);
Vue.use(Option);
