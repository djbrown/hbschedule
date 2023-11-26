$(function () {
    $(".team-toggle").click(function (e) {
        const $target = $(e.target);
        $target.toggleClass("active");

        var activeCount = 0;
        $(".team-toggle").each(function () {
            if ($(this).hasClass("active")) {
                activeCount++;
            }
        });
        const $gameListItems = $('.game-list-item');
        if (activeCount === 0) {
            $gameListItems.show();
        } else {
            const isActive = $target.hasClass("active");
            if (activeCount === 1 && isActive) {
                $gameListItems.hide();
            }
            var teamID = $target.attr("data-team-id");
            const gameListItems = $gameListItems.filter("[data-team-id='" + teamID + "']");
            if (isActive) {
                gameListItems.show();
            } else {
                gameListItems.hide();
            }
        }
    });
    $("#btn-reset-teams").click(function (e) {
        $(".team-toggle").removeClass("active");
        $('.game-list-item').show();
    });
});