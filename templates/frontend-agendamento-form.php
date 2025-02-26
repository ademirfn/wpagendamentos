<form id="agendamento-form">
    <input type="hidden" id="product_id" name="product_id" value="<?php echo get_the_ID(); ?>">

    <label for="data">Escolha a data:</label>
    <input type="date" id="data" name="data" required>
    
    <label for="hora">Escolha o horário:</label>
    <input type="time" id="hora" name="hora" required>
    
    <button type="submit">Agendar</button>
</form>
<div id="agendamento-feedback"></div>
