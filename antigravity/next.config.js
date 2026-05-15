/** @type {import('next').NextConfig} */
const nextConfig = {
  // Enable React strict mode for catching potential issues
  reactStrictMode: true,

  // Optimize images from external sources
  images: {
    remotePatterns: [
      { protocol: 'https', hostname: '**' },
    ],
  },

  // Disable X-Powered-By header for security
  poweredByHeader: false,
};

module.exports = nextConfig;
