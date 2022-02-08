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
    quickAction: (query, origin) => `admin/quickAction?query=${query}&origin=${origin}`
};

export default API;
