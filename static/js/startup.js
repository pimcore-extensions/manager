pimcore.registerNS("pimcore.plugin.extensionmanager");

pimcore.plugin.extensionmanager = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.extensionmanager";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("Example Ready!");
    }
});

var extensionmanagerPlugin = new pimcore.plugin.extensionmanager();

