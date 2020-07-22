import axios from 'axios';
import qs from 'qs';
import BX from 'bitrix';

const service = axios.create({
    timeout: 30000
});

service.interceptors.request.use(
    config => {
        config.headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();

        return config;
    },
    error => {
        console.error(error);
        return Promise.reject(error);
    }
);

service.interceptors.response.use(
    ({ data }) => {
        if (typeof data === 'object' && data.status === 'error') {
            if (Array.isArray(data.errors) && data.errors.length > 0) {
                return Promise.reject(new Error(
                    data.errors
                        .map(error => error.message)
                        .join('\n')
                ));
            }

            return Promise.reject(new Error('Error'));
        } else {
            return data.data;
        }
    },
    error => {
        console.error(error);
        return Promise.reject(error);
    }
);

export const fetchFromModule = (endpoint, data = {}) => {
    if (typeof endpoint !== 'string' || endpoint.length === 0) {
        throw new TypeError('Invalid argument "endpoint"');
    }

    return service({
        url: '/bitrix/services/main/ajax.php',
        method: 'post',
        params: { action: endpoint },
        data: qs.stringify(data, { arrayFormat: 'indices' })
    });
};
