const API = {
    navigation: {
        reset: 'admin/nav/reset',
        save: 'admin/nav/save'
    },
    dashboard: {
        widgets: {
            fetch: 'admin/dashboard/widget',
            save: 'admin/dashboard/widget/save',
            body: 'admin/dashboard/widget/body',
            config: 'admin/dashboard/widget/config',
        }
    },
    session: {
        create: 'admin/session',
        destroy: (token) => `admin/session/${token}/destroy`,
        heartbeat: (token) => `admin/session/${token}/heartbeat`,
        inactive: (token) => `admin/session/${token}/inactive`,
    },
    UI: {
        header: {
            button: {
                create: 'admin/ui/header/button/create',
                search: (query) => `admin/ui/header/button/search?query=${query}`
            }
        }
    }
};

export default API;
