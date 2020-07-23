module.exports = {
    base: '/bitrix-queue/',
    title: 'Bitrix Queue',
    themeConfig: {
        sidebar: [
            {
                title: 'Основы',
                collapsable: false,
                sidebarDepth: 0,
                children: [
                    '/',
                    '/getting-started',
                    '/configuration',
                    '/handlers-registration',
                    '/monitoring',
                ]
            },
            {
                title: 'Продвинуто',
                collapsable: false,
                sidebarDepth: 0,
                children: [
                    '/events',
                    '/transport-factories-registration',
                    '/monitoring-adapters-registration',
                ]
            },
        ]
    }
};