<div class="panel panel-default hidden-print">
    <div class="panel-body panel-recommend">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="titulo-quantidade text-uppercase"><i class="fa fa-share" aria-hidden="true"></i> Indicar Post</h2>
                <div class="row">
                    <div class="col-xs-10">
                        <div id="input-recommend-post"></div>
                        <input type="hidden" name="action" value="recommend_the_post" value="1">
                    </div>
                    <div class="col-xs-2">
                        <button type="submit" class="btn btn-recommend btn-block btn-info" name="submit" id="submit-recommend" data-post-id="<?php echo get_the_ID(); ?>">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>