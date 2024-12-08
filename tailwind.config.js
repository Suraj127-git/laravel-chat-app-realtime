// tailwind.config.js
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/View/Components/**/*.php",
    "./app/Livewire/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        'chat-primary': '#3B82F6',
        'chat-secondary': '#10B981',
        'chat-background': '#F3F4F6'
      }
    },
  },
  plugins: [],
}