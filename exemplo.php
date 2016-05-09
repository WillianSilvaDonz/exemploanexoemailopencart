<?php

class ControllerSiteTrabalheconosco extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('site/trabalho');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('site/trabalheconosco');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			//adiciono as informações na base de dados.
			//$this->model_site_trabalheconosco->addTrabalho($this->request->post);

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			//informações do layout
			$emaildados['titulo'] = 'Maxima Administradora';
			$emaildados['subtitulo'] = 'Condomínio';
			$emaildados['típodeemail'] = 'Trabalhe Conosco';
			$emaildados['contato'] = $this->url->link('information/contact', '', 'SSL');

			//informações pessoais
			$emaildados['nome'] = $this->request->post['trab_nome'];
			$emaildados['email'] = $this->request->post['trab_email'];
			$emaildados['celular'] = $this->request->post['trab_celular'];
			$emaildados['telefone'] = $this->request->post['trab_telefone'];
			//informações endereco
			$emaildados['cep'] = $this->mask($this->request->post['trab_cep'], '#####-###');
			$emaildados['endereco'] = $this->request->post['trab_endereco'];
			$emaildados['bairro'] = $this->request->post['trab_bairro'];
			$emaildados['complemento'] = (($this->request->post['trab_complemento'])?$this->request->post['trab_numero'].' - '.$this->request->post['trab_complemento']:$this->request->post['trab_numero']);
			$emaildados['cidade'] = (($this->request->post['trab_estado'] and $this->request->post['trab_cidade'])?$this->request->post['trab_cidade'].' - '.$this->request->post['trab_estado']:(($this->request->post['trab_cidade'])?$this->request->post['trab_cidade']:(($this->request->post['trab_estado'])?$this->request->post['trab_estado']:'')));
			//experiencia profissionais
			$emaildados['pretendente'] = $this->request->post['area_pretendente'];
			$emaildados['expprofissionais'] = nl2br($this->request->post['trab_mensagem']);
			$emaildados['experiencias'] = array();
			if($this->request->post['experiencia']){
				foreach ($this->request->post['experiencia'] as $experiencia) {
					$emaildados['experiencias'][] = array(
						'empresa' => $experiencia['trab_empresa'],
						'cargo' => $experiencia['trab_cargo'],
						'datainicial' => $experiencia['data_inicio'],
						'datafinal' => (($experiencia['data_final'])?$experiencia['data_final']:'Trabalhando')
					);
				}
			}

			if (isset($this->request->files['trab_curriculum'])) {
				move_uploaded_file($this->request->files['trab_curriculum']['tmp_name'], DIR_DOWNLOAD . $this->request->files['trab_curriculum']['name']);
			}

			if($this->request->files['trab_curriculum']['name']){
                $mail->AddAttachment(DIR_DOWNLOAD.$this->request->files['trab_curriculum']['name']);
            }

			$html = $this->load->view('default/template/site/layoutemailtrabalho.tpl', $emaildados);

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->request->post['trab_email']);
			$mail->setSender(html_entity_decode($this->request->post['trab_nome'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode('Curriculum enviado pelo site por '.$this->request->post['trab_nome'], ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($html);
			$mail->send();

			$htmlusuario = $this->load->view('default/template/site/layoutemailtrabalhousuario.tpl', $emaildados);

			$mail->setTo($this->request->post['trab_email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode('Confirmação de cadastro do curriculum no site maximacondominios.com.br ', ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($htmlusuario);
			//$mail->send();

			//$this->response->redirect($this->url->link('site/trabalheconosco/success'));
		}
	}
}