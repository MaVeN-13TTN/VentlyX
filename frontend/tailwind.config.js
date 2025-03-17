/** @type {import('tailwindcss').Config} */
import headlessui from "@headlessui/tailwindcss";

export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
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
    },
  },
  plugins: [headlessui],
};
