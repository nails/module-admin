import axios from 'axios';

const services = {
    apiRequest(options) {
        let request = {
            method: (options.method || 'GET').toUpperCase(),
            url: `${window.SITE_URL}api/${options.url}`,
            headers: options.headers ? {[options.headers.key]: options.headers.value} : {}
        };

        if (request.method === 'GET') {
            request.params = options.params || options.data;
        } else {
            request.data = options.data;
        }

        return axios(request)
            .then((response) => {
                return response;
            });
    }
};

export default services;
