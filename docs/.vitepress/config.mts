import { defineConfig } from 'vitepress'

export default defineConfig({
  title: "Laravel Query Engine",
  description: "Schema-aware API filtering engine for Laravel.",
  themeConfig: {
    search: {
      provider: 'local'
    },
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/introduction' },
      { text: 'Packagist', link: 'https://packagist.org/packages/victormgomes/laravel-query-engine' }
    ],
    sidebar: [
      {
        text: 'Getting Started',
        items: [
          { text: 'Introduction', link: '/introduction' },
          { text: 'Installation', link: '/installation-and-quick-start' },
          { text: 'Comparison', link: '/package-comparison' },
        ]
      },
      {
        text: 'Usage',
        items: [
          { text: 'URL Syntax & Filters', link: '/1-url-syntax-and-filters' },
          { text: 'Available Methods', link: '/available-methods' },
          { text: 'Advanced Usage', link: '/2-advanced-usage' },
          { text: 'Config & Security', link: '/3-configuration-and-security' }
        ]
      },
      {
        text: 'Resources',
        items: [
          { text: 'Changelog', link: 'https://github.com/victormgomes/laravel-query-engine/releases' },
          { text: 'Upgrading', link: '/UPGRADING' },
        ]
      }
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/victormgomes/laravel-query-engine' }
    ]
  }
})
