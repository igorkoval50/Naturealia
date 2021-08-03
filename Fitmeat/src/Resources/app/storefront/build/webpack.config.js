const { join, resolve } = require('path');
module.exports = () => {
    return {
        resolve: {
            alias: {
                '@swiper': resolve(
                    join(__dirname, '..', 'node_modules', 'swiper')
                ),'dom7': resolve(
                    join(__dirname, '..', 'node_modules', 'dom7')
                ),'ssr-window': resolve(
                    join(__dirname, '..', 'node_modules', 'ssr-window')
                ),

            }
        }
    };
}
