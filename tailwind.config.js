const plugin = require('tailwindcss/plugin')

module.exports = {
  theme: {
    extend: {
      fontFamily: {
        Montserrat: ["Montserrat,sans-serif"],
      },
      maxWidth: {
        "1/2": "50%",
        "30": "30rem"
      },
      width: {
        "full-screen": "100vw",
      },

      height: {
        "80": "20rem",
        "full-screen": "100vh",
      },
      inset: {
        '-10': '-2.75rem',
        '-4': '-0.7rem',
        '6' : '1.5rem',
        '16': '4rem',
        '48': '12rem',
        '80': '20rem',
        '112': '28rem',
        '144': '36rem',
        '176': '44rem'
      },
      borderWidth: {
        '5': '0.3rem',
       '20': '1.25rem',
       '45': '4rem',
      },

      fontSize: {
        "10xl": "10rem",
        "11xl": "12rem",
        "12xl": "14rem",
        "13xl": "16rem",
        "14xl": "18rem",
      },

      minWidth: {
        small: "12rem",
      },
      
      zIndex: {
        99: 99,
      },

      maxWidth: {
        xxs: "12rem",
        "1/2": "50%",
      },

      maxHeight: {
        xxs: "12rem",
        "80": "20rem"
      },

      margin: {
        '7': '1.75rem',
      },

      colors: {
        white: "#FFFFFF",
        dark: "#000000",
        primary: "#333333",
        gray: {
          100: "#EEEEEE",
          200: "#DDDDDD",
          300: "#CCCCCC",
          400: "#AAAAAA",
          500: "#999999",
          600: "#888888",
          700: "#666666",
          800: "#444444",
          900: "#333333",
        },
        brand: {
          lighter: "#fab367",
          default: "#f78e1e",
          darker: "#DB7508",
        },
        secondary: {
          lighter: "#63ACF9",
          default: "#1E87F7",
          darker: "#086EDA",
        },
        transparent: "transparent",
      },

      keyframes: {
        fadein: {
          '0%': { opacity: 0 },
          '100%': { transform: 1 },
        },
        fadeout: {
          '100%': { opacity: 1 },
          '0%': { transform: 0 },
        }
      },

      animation: {
        fadein: 'fadein 0.5s',
        fadeout: 'fadeout 0.5s'
      },
      
      screens: {
        xxsMax:{max: "360px"},
        // => @media (max-width: 360px) { ... }
        baseMax: { max: "640px" },
        // => @media (max-width: 640px) { ... }
        smMax: { max: "768px" },
        // => @media (max-width: 768px) { ... }
        blockMax: { max: "976px" },
        // => @media (max-width: 976px) { ... }
        mdMax: { max: "1024px" },
        // => @media (max-width: 1024px) { ... }
        lgMax: { max: "1200px" },
        // => @media (max-width: 1200px) { ... }
        xlMax: { max: "1535px" },
        // => @media (max-width: 1535px) { ... }
      },
    },
  },
  variants: {},
  corePlugins: {
    container: false,
  },
  plugins: [plugin(function ({ addUtilities, theme, config }) {
    const themeColors = config('theme.colors');
    let individualBorderColors = [];
    Object.keys(themeColors).map(colorName => {
      if(typeof themeColors[colorName] == 'object'){
         Object.keys(themeColors[colorName]).map(color => {
           
          individualBorderColors.push({
            [`.border-b-${colorName}-${color}`]: {
              borderBottomColor: themeColors[colorName][color]
            }
          })
         })
      }else{
        individualBorderColors.push({
          [`.border-b-${colorName}`]: {
            borderBottomColor: themeColors[colorName]
          }
        })
      }
    } );
    addUtilities(individualBorderColors);
  })],
};