const path = require('path')

module.exports = {
    mode: "development",
    entry: "./assets/",
    output: {
        path: path.resolve(__dirname, "www/assets"),
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: ["style-loader", "css-loader", "postcss-loader"],
            },
        ],
    },

    devServer: {
        static: './dist',
    },
}
