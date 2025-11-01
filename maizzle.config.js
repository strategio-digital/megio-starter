/** @type {import('@maizzle/framework').Config} */

export default {
    build: {
        content: ['view/**/*.mail.latte'],
        output: {
            path: 'temp/latte-mail',
            extension: 'latte'
        },
        templates: {
            source: 'view',
            filetypes: ['mail.latte', 'html'],
        }
    },
    components: {
        folders: [
            'view/mail/layout',
            'view/mail/component',
        ],
        tagPrefix: 'x-',
        fileExtension: 'html',
    },
    css: {
        inline: {
            enabled: true,
            removeInlinedSelectors: false,
        },
        purge: false,
        shorthand: true,
        tailwind: {
            config: './maizzle.tailwind.js',
        }
    },
    prettify: false,
}
