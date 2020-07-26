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
                    '/creating-message-handlers',
                    '/transports-registration',
                    '/monitoring',
                ]
            },
            {
                title: 'Продвинуто',
                collapsable: false,
                sidebarDepth: 0,
                children: [
                    '/events',
                    '/monitoring-adapters-registration',
                ]
            },
        ]
    },
    plugins: [
        ['vuepress-plugin-medium-zoom'],
    ],
};