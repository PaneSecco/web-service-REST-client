using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Newtonsoft.Json;

namespace Web_Service_REST
{
    public partial class Form1 : Form
    {
        string baseURL = "http://localhost/Lezione";
        public Form1()
        {
            InitializeComponent();
        }

        private void textBox1_TextChanged(object sender, EventArgs e)
        {

        }

        private async Task vedi()
        {
            MessageBox.Show("winx");
            HttpClient client = new HttpClient();
            string endpoint = "/";

            // Costruzione dell'URL completo
            string requestUrl = baseURL + endpoint;

            // Esempio di richiesta GET
            HttpResponseMessage response = await client.GetAsync(requestUrl);
            if (response.IsSuccessStatusCode)
            {
                MessageBox.Show("Contenuto JSON: " + response);
            }
            else
            {
                MessageBox.Show("Errore nella richiesta: " + response.StatusCode);
            }

        }
        private async void button1_Click(object sender, EventArgs e)
        {
            await vedi();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            
        }
    }
}
