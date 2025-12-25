import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

// ✅ Correct import: mdi (font-based), NOT mdi-svg
import { aliases, mdi } from 'vuetify/iconsets/mdi';

// Your custom icons (if you have any)
import { icons } from './mdi-icon';

// Your theme
import { PurpleTheme } from '@/theme/LightTheme';

// ✅ Important: Import the MDI font CSS
import '@mdi/font/css/materialdesignicons.css';

const vuetify = createVuetify({
  components,
  directives,

  icons: {
    defaultSet: 'mdi',
    aliases: {
      ...aliases,
      ...icons, // Keeps your custom icons working
    },
    sets: {
      mdi, // Font-based iconset
    },
  },

  theme: {
    defaultTheme: 'PurpleTheme',
    themes: {
      PurpleTheme,
    },
  },

  defaults: {
    VBtn: {},
    VCard: {
      rounded: 'md',
    },
    VTextField: {
      rounded: 'lg',
    },
    VTooltip: {
      location: 'top',
    },
  },
});

export default vuetify;