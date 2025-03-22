/** @type {import('tailwindcss').Config} */
import headlessui from "@headlessui/tailwindcss";

export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  darkMode: "class", // Explicitly set darkMode to use class strategy
  theme: {
    container: {
      center: true,
      padding: "0", // Remove padding from container
      screens: {
        sm: "100%",
        md: "100%",
        lg: "100%",
        xl: "100%",
        "2xl": "100%",
      },
    },
    extend: {
      colors: {
        // Light Theme Colors
        primary: {
          DEFAULT: "#FF6B00",
          50: "#FFF4E1",
          100: "#FFE5C4",
          200: "#FFD4A3",
          300: "#FFC282",
          400: "#FFB061",
          500: "#FF6B00",
          600: "#CC5500",
          700: "#993F00",
          800: "#662A00",
          900: "#331500",
        },
        secondary: {
          DEFAULT: "#FFD700",
          50: "#FFF9E6",
          100: "#FFF3CC",
          200: "#FFED99",
          300: "#FFE766",
          400: "#FFE133",
          500: "#FFD700",
          600: "#CCAC00",
          700: "#998100",
          800: "#665600",
          900: "#332B00",
        },
        accent: {
          pink: "#FF1493",
          blue: "#00AEEF",
        },
        background: {
          light: "#FFF4E1",
          dark: "#121212",
        },
        text: {
          light: "#222222",
          dark: "#E0E0E0",
        },
        // Dark Theme Colors
        dark: {
          primary: "#FFB800",
          secondary: "#FF4500",
          accent: {
            pink: "#FF1493",
            blue: "#00BFFF",
          },
        },
      },
      maxWidth: {
        // Remove the content maxWidth restriction
        content: "100%",
      },
      animation: {
        fadeIn: "fadeIn 0.3s ease-in-out",
        "spin-slow": "spin 3s linear infinite",
        "bounce-slow": "bounce 3s infinite",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: 0, transform: "scale(0.7)" },
          "100%": { opacity: 1, transform: "scale(1)" },
        },
      },
      transitionTimingFunction: {
        spring: "cubic-bezier(0.175, 0.885, 0.32, 1.5)",
      },
    },
  },
  corePlugins: {
    container: true,
  },
  plugins: [
    headlessui,
    function ({ addComponents }) {
      addComponents({
        ".center-content": {
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
        },
        // Add a full-width container class
        ".container-full": {
          width: "100%",
          maxWidth: "100%",
          margin: "0 auto",
          padding: "0",
        },
      });
    },
  ],
};
