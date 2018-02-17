$(function()
{
    const steps = $(".register-step");
    const qr = $("#register-qr");
    let global_step = 1;
    let user_id = null;
    
    function showStep(step)
    {
        steps.addClass("hidden-xs-up");
        $("[data-step="+step+"]").removeClass("hidden-xs-up");
    }

    $("#register-next-btn").click(function(e)
    {
        handleStep(global_step);
    });

    function handleStep(step)
    {
        switch(step)
        {
            case 1:
            {
                $.ajax({
                    url: "/smart/api/register_gen_qr.php",
                    method: "POST",
                    data: JSON.stringify({username: $("#register-username").val()}),
                    dataType: "json"
                }).done(function(response)
                {
                    if(response.status === "success")
                    {
                        user_id = response.user_id;
                        global_step++;
                        qr.attr("src", response.qr_url);
                        showStep(global_step);
                    }
                    else
                    {
                        alert(response.error_message);
                    }
                }).fail(function(e)
                {
                    console.error(e);
                });
                break;
            }
            case 2:
            {
                $.ajax({
                    url: "/smart/api/register_check_code.php",
                    method: "POST",
                    data: JSON.stringify({user_id: user_id, code: $("#register-code").val()}),
                    dataType: "json"
                }).done(function(response)
                {
                    if(response.status === "success")
                    {
                        if(response.correct)
                        {
                            global_step++;
                            showStep(global_step);
                            $("#register-next-btn").remove();
                        }
                        else
                        {
                            alert("Invalid code!");
                        }
                    }
                    else
                    {
                        alert(response.error_message);
                    }
                }).fail(function(e)
                {
                    console.error(e);
                });
                break;
            }
        }
    }
});